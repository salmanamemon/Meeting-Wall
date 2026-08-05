# LOOP.md — token-efficient agent workflow

How this repo is worked on with an AI coding agent. The goal is fewer tokens and
less rework, not a perfect framework.

Three pillars: **a code graph** (so the agent reads only what matters), **hard
limits** (so a runaway loop can't cost you), and **a loop** (plan → audit →
implement → cross-audit, with tests as the gate).

---

## 1. Code graph

[code-review-graph](https://github.com/tirth8205/code-review-graph) builds a
Tree-sitter AST of the repo into a local SQLite graph and serves it over MCP.
Instead of re-reading the codebase every task, the agent queries the *blast
radius* of a change — callers, dependents, tests — and reads only those files.
Roughly 8x fewer tokens on average; far more on monorepos.

### One-time setup

```bash
pip install code-review-graph          # or: pipx install code-review-graph
cd /path/to/repo
code-review-graph install --platform claude-code
# restart your editor / agent here
code-review-graph build
code-review-graph status               # sanity check: nodes + edges > 0
```

After the first build the graph updates itself on file save and git commit. You
should never need to run `build` again.

If your editor doesn't support hooks (Cursor, OpenCode), use the daemon instead:

```bash
crg-daemon add . --alias myrepo
crg-daemon start
crg-daemon status
```

### Ignore file

Create `.code-review-graphignore` at the repo root. Git-ignored files are
already skipped; this is for *tracked* files not worth indexing.

```
vendor/**
node_modules/**
generated/**
*.generated.*
```

---

## 2. Sandbox and limits

Run the agent inside a container with hard caps. A loop that goes wrong should
hit a wall, not your laptop or your budget.

```bash
docker run --rm -it \
  --memory=4g --cpus=2 --pids-limit=512 \
  -v "$PWD":/work -w /work \
  your-dev-image bash
```

Then constrain context on the tool side rather than trusting the model to be
frugal:

| Variable | Value | Why |
|---|---|---|
| `CRG_MAX_IMPACT_NODES` | `200` | Default 500; caps blast-radius size |
| `CRG_MAX_IMPACT_DEPTH` | `2` | How far dependency tracing walks |
| `CRG_MAX_BFS_DEPTH` | `8` | Caps free-form traversal |
| `CRG_TOOLS` | see below | Trims the MCP tool surface |

**Tool filtering is the biggest single lever.** CRG exposes 28 MCP tools by
default, and every tool's schema sits in context on *every* turn. Cut it to the
handful you actually use:

```bash
export CRG_TOOLS=get_minimal_context_tool,get_impact_radius_tool,query_graph_tool,detect_changes_tool,get_review_context_tool
```

---

## 3. The loop

Four phases. **Each phase is a separate session** so context doesn't pile up.

### Phase 1 — Plan

Don't hand-write the implementation prompt. Ask a chat model (ChatGPT or Claude
chat, whichever you prefer) to write it for you, then paste it into the coding
agent.

Output: a `PLAN.md` broken into chunks, where **each chunk is small enough to be
one commit**. If a chunk can't be described in two sentences, split it.

### Phase 2 — Audit

New session. Point the agent at the graph and the plan:

> Audit this repo against PLAN.md. What's wrong, what's missing, what breaks?
> Use the graph — check blast radius and knowledge gaps.

Useful tools here: `detect_changes_tool`, `get_knowledge_gaps_tool`,
`get_architecture_overview_tool`.

Fix the plan before any code is written. A bad plan costs ten implementation
runs; a bad audit costs one session.

### Phase 3 — Implement, one chunk at a time

One chunk → one session → one commit. Never "implement the whole plan."

Small chunks mean small context, and a failure costs you one chunk instead of a
full run. This is where the token savings actually land.

### Phase 4 — Cross-audit

Take the agent's final response from the run and paste it into a *different*
chat agent. Ask it to audit.

Different model, fresh eyes, no sunk-cost bias from having written the code.
Anything it flags becomes the next chunk.

Then repeat 3 → 4 per chunk.

---

## 4. Tests as the debug shortcut

Before a chunk is considered done:

- **Integration tests** — do the pieces actually talk to each other
- **Brute-force / edge cases** — bad input, empty input, huge input
- **Rate-limit and timeout paths** — the failure mode you'll hit in production

The point isn't correctness for its own sake. A failing integration test tells
you *where* the problem is. Without it you rebuild from scratch and pay the full
token cost again. **Debugging time is token cost.**

Gate this in CI or a pre-commit hook so it can't be skipped on a tired night.

---

## Quick reference

```bash
code-review-graph status         # graph health
code-review-graph update         # manual incremental update (rarely needed)
code-review-graph detect-changes # risk-scored impact of current diff
code-review-graph visualize      # interactive D3 graph in the browser
code-review-graph eval --all     # reproduce the token benchmarks
```

Slash commands in Claude Code:

| Command | Use |
|---|---|
| `/code-review-graph:build-graph` | Build or rebuild |
| `/code-review-graph:review-delta` | Review changes since last commit |
| `/code-review-graph:review-pr` | Full PR review with blast radius |

---

## Checklist

- [ ] `code-review-graph install` run, editor restarted
- [ ] `build` completed, `status` shows nodes and edges
- [ ] `.code-review-graphignore` in place
- [ ] Hooks working, or daemon running
- [ ] Container caps set (memory, cpus, pids)
- [ ] `CRG_TOOLS` trimmed
- [ ] `PLAN.md` chunked to commit size
- [ ] Plan audited in a fresh session before coding
- [ ] Integration tests gated in CI
