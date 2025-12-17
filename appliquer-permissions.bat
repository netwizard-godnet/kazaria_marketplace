@echo off
echo ========================================
echo MISE A JOUR DU SYSTEME DE PERMISSIONS
echo ========================================
echo.

echo [1/3] Chargement du helper de permissions...
composer dump-autoload
if %errorlevel% neq 0 (
    echo ERREUR: Echec du chargement de l'autoloader
    pause
    exit /b 1
)
echo OK!
echo.

echo [2/3] Creation des nouvelles permissions...
php artisan db:seed --class=PermissionSeeder
if %errorlevel% neq 0 (
    echo ERREUR: Echec de la creation des permissions
    echo Assurez-vous que Laragon est demarre et que MySQL fonctionne
    pause
    exit /b 1
)
echo OK!
echo.

echo [3/5] Mise a jour des roles...
php artisan db:seed --class=RoleSeeder
if %errorlevel% neq 0 (
    echo ERREUR: Echec de la mise a jour des roles
    pause
    exit /b 1
)
echo OK!
echo.

echo [4/5] Vidage du cache...
php artisan cache:clear
php artisan view:clear
php artisan config:clear
if %errorlevel% neq 0 (
    echo ERREUR: Echec du vidage du cache
    pause
    exit /b 1
)
echo OK!
echo.

echo [5/5] Verification du systeme...
php diagnostic-permissions.php
echo.

echo ========================================
echo MISE A JOUR TERMINEE AVEC SUCCES!
echo ========================================
echo.
echo Les permissions ont ete correctement configurees.
echo Vous pouvez maintenant vous reconnecter au dashboard admin.
echo.
pause

