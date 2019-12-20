@ECHO OFF
setlocal DISABLEDELAYEDEXPANSION
SET BIN_TARGET=%~dp0/../endroid/test/bin/functional-test
bash "%BIN_TARGET%" %*
