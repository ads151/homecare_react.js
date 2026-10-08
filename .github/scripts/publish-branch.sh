#!/usr/bin/env bash
# Usage: publish-branch.sh <source-folder> <branch>
# Copies <source-folder> to the root of <branch> and pushes one new commit.
# History is kept (no force push), so Hostinger "git pull" always works.
set -euo pipefail
SRC="$1"
BRANCH="$2"
WORK="$(mktemp -d)"

git config --global user.name "github-actions[bot]"
git config --global user.email "41898282+github-actions[bot]@users.noreply.github.com"

if git ls-remote --exit-code --heads origin "$BRANCH" >/dev/null 2>&1; then
  git fetch --depth=1 origin "$BRANCH"
  git worktree add "$WORK" "origin/$BRANCH"
  (cd "$WORK" && git checkout -B "$BRANCH")
else
  git worktree add --detach "$WORK"
  (cd "$WORK" && git checkout --orphan "$BRANCH" && git rm -rfq . || true)
fi

# Replace everything except .git with the new build
find "$WORK" -mindepth 1 -maxdepth 1 ! -name .git -exec rm -rf {} +
cp -a "$SRC"/. "$WORK"/

cd "$WORK"
git add -A -f
if git diff --cached --quiet; then
  echo "No changes for $BRANCH"
  exit 0
fi
git commit -q -m "Deploy ${GITHUB_SHA:0:7} from main"
git push origin "$BRANCH"
echo "Published $BRANCH"
