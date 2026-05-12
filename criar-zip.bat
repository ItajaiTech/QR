@echo off
REM Script para compactar o plugin QR Etiqueta
REM Use este script para criar um arquivo ZIP para enviar ao WordPress

echo.
echo ========================================
echo QR Etiqueta - Criador de ZIP
echo ========================================
echo.

set SOURCE=qr-etiqueta-plugin
set DESTINATION=qr-etiqueta-plugin.zip

if not exist "%SOURCE%" (
    echo ERRO: Pasta "%SOURCE%" nao encontrada!
    echo Coloque este script na mesma pasta que contem "qr-etiqueta-plugin"
    pause
    exit /b 1
)

echo Compactando "%SOURCE%"...
echo.

REM Remover ZIP antigo se existir
if exist "%DESTINATION%" (
    echo Removendo arquivo anterior: "%DESTINATION%"
    del "%DESTINATION%"
)

REM Criar novo ZIP usando PowerShell (mais confiavel)
powershell -noProfile -command "Compress-Archive -Path '%SOURCE%' -DestinationPath '%DESTINATION%' -Force"

if %errorlevel% equ 0 (
    echo.
    echo ========================================
    echo ✓ Sucesso!
    echo ✓ Arquivo criado: %DESTINATION%
    echo ========================================
    echo.
    echo Proximo passo:
    echo 1. Abra seu WordPress
    echo 2. Vá a: Plugins ^> Adicionar Novo
    echo 3. Clique em "Enviar plugin"
    echo 4. Selecione: %DESTINATION%
    echo 5. Clique em "Instalar Agora"
    echo 6. Clique em "Ativar"
    echo.
    pause
) else (
    echo.
    echo ERRO: Falha ao criar o arquivo ZIP!
    echo Código de erro: %errorlevel%
    pause
    exit /b 1
)
