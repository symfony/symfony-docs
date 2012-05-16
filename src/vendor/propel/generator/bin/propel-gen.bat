@echo off

rem *********************************************************************
rem ** The Propel generator convenience script for Windows based systems
rem ** $Id$
rem *********************************************************************

rem This script will do the following:
rem - check for PHING_COMMAND env, if found, use it.
rem   - if not found detect php, if found use it, otherwise err and terminate
rem - check for PROPEL_GEN_HOME env, if found use it
rem   - if not found error and leave

if "%OS%"=="Windows_NT" @setlocal

rem %~dp0 is expanded pathname of the current script under NT
set DEFAULT_PROPEL_GEN_HOME=%~dp0..

if "%PROPEL_GEN_HOME%" == "" set PROPEL_GEN_HOME=%DEFAULT_PROPEL_GEN_HOME%
set DEFAULT_PROPEL_GEN_HOME=

if "%PHING_COMMAND%" == "" set PHING_COMMAND=phing.bat

set nbArgs=0
for %%x in (%*) do Set /A nbArgs+=1
if %nbArgs% leq 1 (
  %PHING_COMMAND% -f "%PROPEL_GEN_HOME%\build.xml" -Dusing.propel-gen=true -Dproject.dir="%CD%" %*
) else (
  %PHING_COMMAND% -f "%PROPEL_GEN_HOME%\build.xml" -Dusing.propel-gen=true -Dproject.dir=%*
)

if "%OS%"=="Windows_NT" @endlocal
