# 🎫 QR Etiqueta Argox - Plugin WordPress

Plugin completo para gerar QR codes otimizados para impressão em etiquetas Argox 2140 (106mm x 52mm) com sequências de 10 números.

## 📦 Conteúdo do Plugin

```
qr-etiqueta-plugin/
├── qr-etiqueta.php           # Arquivo principal do plugin
├── index.php                 # Proteção de diretório
├── assets/
│   ├── index.php             # Proteção de diretório
│   ├── admin-qr.css          # Estilos do painel admin
│   ├── admin-qr.js           # JavaScript do admin
│   ├── frontend-qr.css       # Estilos do frontend
│   └── frontend-qr.js        # JavaScript do frontend
├── includes/
│   ├── index.php             # Proteção de diretório
│   ├── qr-generator.php      # Classe geradora de QR codes
│   └── functions.php         # Funções auxiliares
├── templates/
│   ├── index.php             # Proteção de diretório
│   ├── admin-pages.php       # Páginas de administração
│   └── shortcode-qr.php      # Template do shortcode
└── README.md                 # Este arquivo
```

## ✅ Funcionalidades

### 📱 Geração de QR Code
- ✅ Input validado de 10 dígitos
- ✅ QR code gerado via Google Charts API (confiável)
- ✅ Visualização em tempo real
- ✅ Histórico completo de códigos gerados

### 🖨️ Impressão em Etiqueta
- ✅ Layout otimizado para 106x52mm
- ✅ Compatível com impressora Argox 2140
- ✅ Formatação automática do número (XXX-XXX-XXXX)
- ✅ Timestamp de criação
- ✅ Página de impressão com CSS para etiqueta

### 📊 Painel Administrativo
- ✅ Interface intuitiva com duas abas
- ✅ Geração individual de QR codes
- ✅ Histórico com paginação
- ✅ Exportação de dados
- ✅ Limpeza de histórico

### 🔐 Segurança
- ✅ Proteção ABSPATH
- ✅ Verificação de nonce
- ✅ Sanitização de inputs
- ✅ Escape de outputs
- ✅ Validação de permissões

### 🎯 Shortcode para Frontend
- ✅ `[qr_etiqueta]` para usar em páginas/posts
- ✅ Mesmo formulário do admin disponível para usuários

## 🚀 Instalação

### Opção 1: Instalação Manual

1. **Copiar pasta do plugin**
   ```
   wp-content/plugins/qr-etiqueta-plugin/
   ```

2. **Ativar no WordPress**
   - Ir para: Plugins > Plugins Instalados
   - Procurar por "QR Etiqueta Argox"
   - Clique em "Ativar"

### Opção 2: Compactar e Enviar

1. **Criar arquivo ZIP da pasta**
   ```
   qr-etiqueta-plugin.zip
   ```

2. **Fazer upload no WordPress**
   - Plugins > Adicionar Novo > Enviar plugin
   - Envie `qr-etiqueta-plugin.zip`
   - Instale e ative

## 📖 Como Usar

### 👨‍💼 Para Administradores

#### Gerar QR Code (Painel Admin)

1. Acesse: **QR Etiqueta > Gerar QR**
2. Digite um código de **10 dígitos** (ex: `1234567890`)
3. Clique em **"Gerar QR Code"**
4. O código aparece na visualização
5. Clique em **"Imprimir Etiqueta"** para abrir a página de impressão

#### Visualizar Histórico

1. Acesse: **QR Etiqueta > Histórico**
2. Veja todos os códigos gerados com data e usuário
3. Use os botões para:
   - **Imprimir**: Abrir página de impressão
   - **Limpar Histórico**: Excluir todos os registros

#### Configurações

1. Acesse: **QR Etiqueta > Configurações**
2. Informações técnicas do plugin
3. Instruções passo a passo
4. Dicas e melhores práticas

### 👥 Para Usuários Finais (Frontend)

Use o shortcode em qualquer página/post:

```html
[qr_etiqueta]
```

Isto adiciona um formulário onde usuários podem:
1. Digitar um código de 10 dígitos
2. Gerar o QR code
3. Imprimir a etiqueta diretamente

## 🖨️ Como Imprimir em Etiqueta Argox 2140

### Passo a Passo

1. **Carregar etiquetas na impressora**
   - Dimensões: 106mm x 52mm
   - Coloque as etiquetas no suporte

2. **Gerar QR code**
   - No painel do WordPress, gere o código desejado

3. **Abrir página de impressão**
   - Clique em "Imprimir Etiqueta"
   - A página abrirá em nova aba

4. **Configurar impressora**
   - Abra o diálogo de impressão (Ctrl+P ou Cmd+P)
   - Selecione: **Impressora Argox 2140**
   - Tamanho do papel: **106x52mm** (personalizado)
   - Margens: **None/Nenhuma**
   - Escala: **100%**

5. **Configurações recomendadas**
   ```
   Printer: Argox 2140
   Paper Size: 106x52 mm
   Orientation: Landscape
   Margins: None
   Scale: 100%
   Quality: Best/Máxima
   ```

6. **Imprimir**
   - Clique em "Imprimir"
   - Ajuste a primeira etiqueta se necessário
   - Imprima em lote

### 💡 Dicas Práticas

- ✓ Teste com 3-5 etiquetas antes do lote
- ✓ Se o QR code não escanear, aumente a qualidade de impressão
- ✓ Use adesivo de qualidade para melhor durabilidade
- ✓ Guarde os números no histórico para referência

## 🗄️ Banco de Dados

O plugin cria uma tabela automática para rastreamento:

```sql
wp_qr_etiqueta_historico (
  id: ID único
  codigo: 10 dígitos
  data_criacao: Data/hora de criação
  usuario_id: ID do usuário que criou
  observacoes: Campo para anotações (futuro)
)
```

## ⚙️ Requisitos

- **WordPress**: 5.8 ou superior
- **PHP**: 7.4 ou superior
- **Extensões**: Nenhuma dependência obrigatória
- **Permissão**: Acesso administrativo (admin)

## 🔧 Configuração Técnica

### Hooks Utilizados

- `activation_hook`: Criar tabela de histórico
- `deactivation_hook`: Limpar opções
- `admin_menu`: Adicionar menu administrativo
- `wp_ajax_qr_etiqueta_gerar_qr`: AJAX para gerar QR
- `wp_enqueue_scripts`: Carregar assets
- `wp_shortcodes`: Registrar shortcode

### Endpoints AJAX

```
POST wp-admin/admin-ajax.php
  - action: qr_etiqueta_gerar_qr
  - codigo: 10 dígitos
  - nonce: verificação de segurança
```

## 📝 Estrutura de Código

### Classe Principal: `QR_Etiqueta_Generator`

```php
// Gerar QR code
$generator = new QR_Etiqueta_Generator();
$qr = $generator->gerar('1234567890');

// Gerar HTML de impressão
$html = $generator->gerar_html_impressao($codigo, $image_url);
```

## 🐛 Troubleshooting

### "Erro ao gerar QR code"
- Verifique a conexão com internet (usamos Google Charts)
- O navegador bloqueou conteúdo híbrido? Desative para o site

### "Impressão saindo pequena"
- Verifique se as margens estão em "Nenhuma"
- Configure o tamanho personalizado em 106x52mm
- Altere a escala para 100% exatamente

### "QR code não escaneia"
- Aumente a qualidade de impressão na impressora
- Use etiquetas de melhor qualidade
- Teste em navegador diferente

### Dados não salvando no histórico
- Verifique permissões do banco de dados WordPress
- Confirme se as tabelas foram criadas: `wp_qr_etiqueta_historico`
- Ative modo de debug do WordPress

## 📋 Changelog

### v1.0.0 (Inicial)
- ✅ Geração de QR codes com 10 dígitos
- ✅ Painel administrativo completo
- ✅ Shortcode para frontend
- ✅ Histórico de códigos
- ✅ Layout otimizado 106x52mm
- ✅ Página de impressão
- ✅ Segurança e sanitização

## 📞 Suporte

Para problemas ou dúvidas:

1. Verifique este README
2. Ative modo de debug do WordPress
3. Procure no histórico do plugin
4. Verifique a compatibilidade do navegador

## 📄 Licença

GPL v2 ou posterior

## 👨‍💻 Desenvolvido para

Impressora Argox 2140 / Etiqueta 106x52mm
WordPress 5.8+
