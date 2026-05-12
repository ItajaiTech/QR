# Changelog - Download de PDF para Etiquetas QR

## Alterações Implementadas

### Novos Arquivos
- `includes/fpdf.php` - Biblioteca FPDF para geração de PDF (versão minimalista)

### Arquivos Modificados

#### 1. includes/qr-generator.php
- Adicionado `require_once` para carregar FPDF
- **Nova função:** `gerar_pdf_download($qr_data, $image_url)` - Gera e envia PDF para download
- **Nova função:** `download_qr_image($url)` - Baixa imagem do QR para arquivo temporário

#### 2. qr-etiqueta.php
- **Novo endpoint AJAX:** `qr_etiqueta_download_pdf` - Handler para download de PDF
- Registrado com `wp_ajax` e `wp_ajax_nopriv` para funcionar tanto no admin quanto no frontend

#### 3. assets/admin-qr.js
- Adicionada variável `$downloadPdfBtn` para o botão de download
- **Nova função:** `baixarPDF(data)` - Abre nova aba para download do PDF
- Listener de click para botão de download PDF

#### 4. assets/frontend-qr.js
- Adicionada variável `$downloadPdfBtn` para o botão de download
- **Nova função:** `baixarPDF()` - Abre nova aba para download do PDF
- Listener de click para botão de download PDF

#### 5. templates/admin-pages.php
- Adicionado botão "Baixar PDF" (`id="download-pdf-btn"`)

#### 6. templates/shortcode-qr.php
- Adicionado botão "Baixar PDF" (`id="download-pdf-btn-frontend"`)

## Como Funciona

1. Usuário gera QR code normalmente
2. Além do botão "Imprimir", agora há o botão "Baixar PDF"
3. Ao clicar em "Baixar PDF":
   - JavaScript faz requisição GET para `qr_etiqueta_download_pdf`
   - Backend baixa a imagem do QR do QuickChart
   - FPDF cria PDF de 106x52mm (tamanho da etiqueta Argox 2140)
   - QR é posicionado 50x50mm no canto superior esquerdo
   - PDF é enviado para download com nome `etiqueta-qr-{timestamp}.pdf`

## Vantagens do PDF

- **Qualidade garantida:** PDF mantém resolução e proporções exatas
- **Sem problemas de impressão web:** Navegador não altera escalonamento
- **Padronização:** Sempre 106x52mm independente do navegador
- **Portabilidade:** Pode salvar e imprimir depois
- **Lote:** Cada PDF pode ser facilmente organizado para impressão posterior

## Próximos Passos

1. **Upload para servidor:** Copie todos os arquivos para o WordPress
2. **Teste:** Gere um QR code e clique em "Baixar PDF"
3. **Impressão:** Abra o PDF e imprima na Argox 2140 usando Adobe Reader ou similar
4. **Verificar:** Bipar o QR impresso para validar se números aparecem corretos com quebras de linha

## Observação Importante

O PDF é gerado com a imagem do QR em 600x600px, que garante boa qualidade para impressão. O tamanho físico no PDF é 50x50mm, otimizado para leitura na Argox 2140 (203dpi).
