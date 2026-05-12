# 🚀 Guia de Instalação Rápida - QR Etiqueta Argox

## 1️⃣ Instalação Método Direto

### Passo 1: Copiar a Pasta do Plugin

```
C:\RelogioPonto\QR\qr-etiqueta-plugin\
     ↓
wp-content/plugins/qr-etiqueta-plugin/
```

### Passo 2: Ativar no WordPress

1. Abra o WordPress (painel admin)
2. Vá para: **Plugins > Plugins Instalados**
3. Procure por "**QR Etiqueta Argox**"
4. Clique em **"Ativar"**

### Passo 3: Começar a Usar

1. No menu esquerdo do WordPress, aparecerá: **QR Etiqueta**
2. Clique em "**QR Etiqueta > Gerar QR**"
3. Digite um código de 10 dígitos
4. Clique em "**Gerar QR Code**"
5. Clique em "**Imprimir Etiqueta**"

## 2️⃣ Instalação via Método Compactado

### Passo 1: Criar o ZIP

```powershell
# No PowerShell, na pasta do plugin:
Compress-Archive -Path "C:\RelogioPonto\QR\qr-etiqueta-plugin" -DestinationPath "C:\RelogioPonto\QR\qr-etiqueta-plugin.zip"
```

### Passo 2: Upload no WordPress

1. WordPress: **Plugins > Adicionar Novo**
2. Clique em: **"Enviar plugin"**
3. Selecione o arquivo: `qr-etiqueta-plugin.zip`
4. Clique em: **"Instalar"**
5. Clique em: **"Ativar"**

## 3️⃣ Verificação Pós-Instalação

### ✅ Checklist

- [ ] Menu "QR Etiqueta" aparece no admin
- [ ] Submenu "Gerar QR" existe
- [ ] Submenu "Histórico" existe
- [ ] Submenu "Configurações" existe
- [ ] Carregar página "Gerar QR" funciona
- [ ] Formulário de entrada aparece
- [ ] Campo aceita 10 dígitos

### 🧪 Teste Prático

1. Acesse: **QR Etiqueta > Gerar QR**
2. Digite: `1234567890`
3. Clique em: **"Gerar QR Code"**
4. Aguarde o QR code aparecer
5. Clique em: **"Imprimir Etiqueta"**
6. Verifique a página de impressão

## 4️⃣ Configuração da Impressora Argox 2140

### No Windows

1. **Painel de Controle > Dispositivos e Impressoras**
2. Clique com direito na **Argox 2140**
3. **"Preferências de Impressão"**

### Configurações Recomendadas

```
Tipo de Papel: Personalizado 106x52mm
Orientação: Paisagem
Margens: Nenhuma (0)
Qualidade: Máxima
Zoom: 100%
```

## 5️⃣ Como Usar no Frontend

### Adicionar em Página/Post

1. **Páginas > Adicionar Nova** (ou editar existente)
2. Digite no conteúdo:
   ```
   [qr_etiqueta]
   ```
3. Publique a página
4. Usuários poderão usar o formulário ali

## 6️⃣ Troubleshooting

### ❌ Plugin não aparece no menu

**Solução:**
- Ir para: **Plugins > Plugins Instalados**
- Procurar por "QR Etiqueta"
- Clicar em **"Ativar"**

### ❌ Erro ao gerar QR

**Verifique:**
- Internet conectada (usamos API do Google)
- Código tem exatamente 10 dígitos
- Browser permite conteúdo híbrido HTTP/HTTPS

### ❌ Impressão saindo pequenininha

**Solução:**
1. Ctrl+P (abrir impressão)
2. Tamanho: **106x52mm** (customizado)
3. Margens: **Nenhuma**
4. Escala: **100%**
5. **Imprimir**

## 📞 Suporte Rápido

| Problema | Solução |
|----------|---------|
| Menu não aparece | Ativar plugin |
| QR não gera | Verificar internet |
| Dados não salvam | Check DB permissions |
| Impressão errada | Configurar tamanho 106x52mm |

## 📋 Requisitos Mínimos

- ✅ WordPress 5.8+
- ✅ PHP 7.4+
- ✅ Argox 2140 instalada

## 🎉 Pronto!

O plugin está funcionando! 

**Próximas ações:**
- Gere alguns QR codes
- Teste a impressão
- Verifique o histórico
- Compartilhe com os usuários
