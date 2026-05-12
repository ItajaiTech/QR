@echo off
REM Verificação de estrutura do plugin QR Etiqueta
REM Execute este arquivo para verificar se tudo foi criado corretamente

echo.
echo ╔═══════════════════════════════════════════════════════════╗
echo ║  Plugin QR Etiqueta - Verificador de Estrutura           ║
echo ╚═══════════════════════════════════════════════════════════╝
echo.

set PASS=0
set FAIL=0

echo ✓ Verificando estrutura de pasta...
echo.

REM Verificar pasta raiz
if exist "qr-etiqueta-plugin\" (
    echo [OK] Pasta "qr-etiqueta-plugin" existe
    set /a PASS+=1
) else (
    echo [ERRO] Pasta "qr-etiqueta-plugin" NAO encontrada
    set /a FAIL+=1
)

REM Verificar subpastas
for %%D in (assets includes templates) do (
    if exist "qr-etiqueta-plugin\%%D\" (
        echo [OK] Pasta "qr-etiqueta-plugin\%%D" existe
        set /a PASS+=1
    ) else (
        echo [ERRO] Pasta "qr-etiqueta-plugin\%%D" NAO encontrada
        set /a FAIL+=1
    )
)

echo.
echo ✓ Verificando arquivos PHP...
echo.

REM Verificar arquivos PHP principais
for %%F in (qr-etiqueta.php README.md validate-plugin.php index.php) do (
    if exist "qr-etiqueta-plugin\%%F" (
        echo [OK] "%%F"
        set /a PASS+=1
    ) else (
        echo [ERRO] "%%F" NAO encontrado
        set /a FAIL+=1
    )
)

echo.
echo ✓ Verificando arquivos em "assets"...
echo.

for %%F in (admin-qr.css admin-qr.js frontend-qr.css frontend-qr.js index.php) do (
    if exist "qr-etiqueta-plugin\assets\%%F" (
        echo [OK] "assets\%%F"
        set /a PASS+=1
    ) else (
        echo [ERRO] "assets\%%F" NAO encontrado
        set /a FAIL+=1
    )
)

echo.
echo ✓ Verificando arquivos em "includes"...
echo.

for %%F in (qr-generator.php functions.php index.php) do (
    if exist "qr-etiqueta-plugin\includes\%%F" (
        echo [OK] "includes\%%F"
        set /a PASS+=1
    ) else (
        echo [ERRO] "includes\%%F" NAO encontrado
        set /a FAIL+=1
    )
)

echo.
echo ✓ Verificando arquivos em "templates"...
echo.

for %%F in (admin-pages.php shortcode-qr.php index.php) do (
    if exist "qr-etiqueta-plugin\templates\%%F" (
        echo [OK] "templates\%%F"
        set /a PASS+=1
    ) else (
        echo [ERRO] "templates\%%F" NAO encontrado
        set /a FAIL+=1
    )
)

echo.
echo ═════════════════════════════════════════════════════════
echo.
echo Resultado:
echo   ✓ Arquivos OK: %PASS%
echo   ✗ Arquivos faltando: %FAIL%
echo.

if %FAIL% equ 0 (
    echo ✓ Estrutura CORRETA! Plugin pronto para instalar.
    echo.
    echo Próximo passo:
    echo   1. Copie a pasta "qr-etiqueta-plugin" para:
    echo      wp-content/plugins/
    echo   2. Ative o plugin no WordPress
    echo   3. Comece a usar!
) else (
    echo ✗ Estrutura INCOMPLETA! Verifique os arquivos.
)

echo.
pause
