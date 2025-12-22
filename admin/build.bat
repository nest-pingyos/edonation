@echo off
echo ================================================
echo   eDonation Admin - Build Assets
echo ================================================
echo.

cd /d %~dp0

:: Check if node_modules exists
if not exist "node_modules" (
    echo [1/3] Installing npm packages...
    echo This may take a few minutes...
    call npm install
    if errorlevel 1 (
        echo.
        echo ERROR: npm install failed!
        echo Please make sure Node.js is installed.
        pause
        exit /b 1
    )
    echo Done!
) else (
    echo [1/3] npm packages already installed. Skipping...
)

echo.
echo [2/3] Building assets with Gulp...
call npx gulp build
if errorlevel 1 (
    echo.
    echo ERROR: Gulp build failed!
    pause
    exit /b 1
)

echo.
echo [3/3] Build completed successfully!
echo.
echo ================================================
echo   Assets are ready in src/assets/
echo ================================================
echo.
echo You can now access the admin panel:
echo http://localhost/appdev/edonation/admin/src/
echo.
pause
