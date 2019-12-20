@ECHO OFF
setlocal DISABLEDELAYEDEXPANSION
SET BIN_TARGET=%~dp0/../endroid/test/bin/test
bash "%BIN_TARGET%" %*
