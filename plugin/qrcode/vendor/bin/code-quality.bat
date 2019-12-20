@ECHO OFF
setlocal DISABLEDELAYEDEXPANSION
SET BIN_TARGET=%~dp0/../endroid/test/bin/code-quality
bash "%BIN_TARGET%" %*
