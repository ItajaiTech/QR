# Script PowerShell para compactar o plugin QR Etiqueta
# Execute: .\criar-zip.ps1

# Definir variáveis
$sourceFolder = "$PSScriptRoot\qr-etiqueta-plugin"
$zipFile = "$PSScriptRoot\qr-etiqueta-plugin.zip"

# Verificar se a pasta existe
if (-not (Test-Path $sourceFolder)) {
    Write-Error "Pasta '$sourceFolder' não encontrada!"
    exit 1
}

# Remover arquivo ZIP antigo se existir
if (Test-Path $zipFile) {
    Remove-Item $zipFile -Force
    Write-Host "✓ Arquivo antigo removido" -ForegroundColor Green
}

# Compactar
Write-Host "Compactando plugin..." -ForegroundColor Cyan
Compress-Archive -Path $sourceFolder -DestinationPath $zipFile -Force

if ($?) {
    Write-Host "`n✓ Sucesso!" -ForegroundColor Green
    Write-Host "✓ Arquivo criado: $zipFile" -ForegroundColor Green
    
    $size = (Get-Item $zipFile).Length / 1MB
    Write-Host "✓ Tamanho: $([Math]::Round($size, 2)) MB" -ForegroundColor Green
    
    Write-Host "`nPróximos passos:" -ForegroundColor Cyan
    Write-Host "1. Abra seu WordPress (painel admin)"
    Write-Host "2. Plugins > Adicionar Novo"
    Write-Host "3. Clique em 'Enviar plugin'"
    Write-Host "4. Selecione: $zipFile"
    Write-Host "5. Clique em 'Instalar Agora'"
    Write-Host "6. Clique em 'Ativar'"
    Write-Host ""
} else {
    Write-Error "Falha ao criar o arquivo ZIP!"
    exit 1
}

# Abrir pasta contendo o arquivo ZIP (opcional)
explorer /select,$zipFile
