# 🖨️ Guia Completo - Impressora Argox 2140 + QR Etiqueta

## 📋 Índice

1. [Configuração Básica](#configuração-básica)
2. [Dimensões e Tipos de Etiqueta](#dimensões-e-tipos-de-etiqueta)
3. [Configurar Tamanho de Papel no Windows](#configurar-tamanho-de-papel-no-windows)
4. [Imprimir via WordPress](#imprimir-via-wordpress)
5. [Dicas e Melhores Práticas](#dicas-e-melhores-práticas)
6. [Troubleshooting](#troubleshooting)

---

## ⚙️ Configuração Básica

### Hardware

```
Modelo: Argox 2140
Resolução: 203 DPI (8 pontos/mm)
Velocidade: Até 100mm/segundo
Conexão: USB / Ethernet / Serial
Suporte de Etiqueta: 24-127mm de altura
```

### Especificações de Etiqueta

```
Tamanho Desejado: 106mm x 52mm (Landscape/Paisagem)
Área de Impressão: ~100mm x 46mm (margem de 2-3mm)
Tipo: Adesivo (4x6, 4x2.5 - formato imperial)
Suporte: Detecção automática (gap, preta)
```

---

## 📐 Dimensões e Tipos de Etiqueta

### Tamanho Padrão 106x52mm

```
┌─────────────────────────────────────┐
│ PAISAGEM RECOMENDADO                │
└─────────────────────────────────────┘
Largura: 106mm (4,17 polegadas)
Altura: 52mm (2,05 polegadas)
Orientação: Landscape

Comparação com tamanhos comuns:
- 4x2 = 101,6 x 50,8mm  (muito próximo!)
- 4x3 = 101,6 x 76,2mm  (muito alto)
- 4x6 = 101,6 x 152,4mm (muito alto)
```

### Tipos Disponíveis

| Tipo | Tamanho | Melhor Para |
|------|---------|------------|
| 4x2 | ~106x52mm | QR Etiqueta ✓ |
| 4x3 | ~106x76mm | QR Etiqueta (com espaço) |
| 4x4 | ~106x102mm | Etiqueta grande |
| 4x6 | ~106x152mm | Etiqueta extra grande |

---

## 🖥️ Configurar Tamanho de Papel no Windows

### Método 1: Via Preferências de Impressão (Recomendado)

1. **Abrir Dispositivos e Impressoras**
```
Windows:
  Painel de Controle
  > Dispositivos e Impressoras
```

2. **Acessar Preferências da Argox 2140**
```
  Clique direito na "Argox 2140"
  > Preferências de Impressão
  (ou Propriedades)
```

3. **Configurar Tamanho do Papel**
```
Aba: Papel
└─ Tamanho: Procure por:
   ✓ 4x2" (se disponível)
   ✓ 106x52mm (se disponível)
   ✓ Ou crie personalizado
```

4. **Se Não Existir, Criar Personalizado**
```
Clique: "Nova Página"
Name: QR Etiqueta 106x52
Largura: 106mm (ou 4,17 pol)
Altura: 52mm (ou 2,05 pol)
Margens: 0mm (todas)
Salvar
```

5. **Orientação e Margens**
```
Aba: Layout
└─ Orientation: Paisagem/Landscape
└─ Margens: Nenhuma/None (0mm)
```

6. **Qualidade**
```
Aba: Geral ou Qualidade
└─ Temperatura: Normal/Médio
└─ Velocidade: Máxima (se papel fino)
└─ Resolução: 203 DPI (máxima)
└─ Densidade: 8 (padrão)
```

### Método 2: Via Windows Settings

```
Configurações > Dispositivos > Impressoras
> Argox 2140
> Preferências
> Tamanho do Papel Personalizado
```

---

## 📱 Imprimir via WordPress

### Passo a Passo Completo

#### 1. Gerar QR Code no WordPress

```
WordPress Admin:
  QR Etiqueta
  └─ Gerar QR
```

```
Formulário:
  Código (10 dígitos): 1234567890
  Clique: "Gerar QR Code"
```

#### 2. Visualizar QR Code

```
Resultado:
  ✓ QR Code aparece
  ✓ Número formatado (123-456-7890)
  ✓ Data/hora de criação
```

#### 3. Abrir Página de Impressão

```
Clique: "Imprimir Etiqueta"
  └─ Abre nova aba
  └─ Exibe layout da etiqueta
  └─ Pronto para imprimir
```

#### 4. Configurar Impressão

**Abrir Diálogo de Impressão:**
```
Teclado: Ctrl+P (Windows/Linux)
         Cmd+P (Mac)
Ou: Menu > Imprimir
```

**Configurações Críticas:**

```
Impressora: Argox 2140 ✓
Tamanho do Papel: 106x52mm (personalizado) ✓
Orientação: PAISAGEM ✓
Margens: NENHUMA (0mm) ✓
Escala: 100% EXATA ✓
Qualidade: Máxima ✓
```

#### 5. Visualizar Antes de Imprimir

```
Browser:
  Firefox/Chrome: Print Preview (abas)
  Edge: "Mais configurações"
```

**Checklist visual:**
- [ ] Layout está 106x52mm
- [ ] Margens são mínimas
- [ ] QR Code está legível
- [ ] Números estão visíveis
- [ ] Sem cortes ou compressão

#### 6. Imprimir

```
1. Coloque etiquetas na Argox 2140
2. Verifique orientação (landscape)
3. Clique: "Imprimir"
4. Aguarde conclusão
```

---

## 💡 Dicas e Melhores Práticas

### ✓ Antes de Começar

- [ ] Etiquetas: Verificar se são 4x2" (ou 106x52mm)
- [ ] Impressora: Ligar e aquecer por 5 minutos
- [ ] Driver: Atualizar para versão mais recente
- [ ] Teste: Imprimir página de teste na Argox

### ✓ Configuração para Melhor Resultado

```
SITE > WORDPRESS           PRINTER
└─ Plugin                  └─ Argox 2140
   └─ Formulário              └─ Temperatura: Normal
      └─ Código 10 dígitos       └─ Velocidade: 100% (rápido)
         └─ Gerar                   └─ Resolução: 203 DPI
            └─ Visualizar           └─ Densidade: 8-10
               └─ Imprimir (Ctrl+P) └─ Modo: Contínuo ou Gap
```

### ✓ Primeiro Teste

```
1. Imprima UMA etiqueta
2. Deixe secar (5-10 segundos)
3. Teste com scanner/leitor
4. Se OK, imprima mais
5. Se não, ajuste (próximo tópico)
```

### ✓ Impressão em Lote

```
Para imprimir muitas etiquetas:

1. Carregue 50-100 etiquetas
2. Configure fila no WordPress/Windows
3. Pense em: Fazer 5-10, testar, depois o resto
4. Monitor: Verificar papéis de vez em quando
```

### ✓ Armazenamento de Histórico

```
Use o histórico do plugin para:
└─ Consultar códigos impressos
└─ Não reimprimir número igual
└─ Rastreabilidade
└─ Backup de dados
```

---

## 🐛 Troubleshooting

### ❌ Problema: Impressão muito pequena

**Causas:**
- Margens muito grandes
- Escala configurada < 100%
- Tamanho de papel incorreto

**Solução:**
```
1. Abrir print (Ctrl+P)
2. Trocar Tamanho para: 106x52mm (exato)
3. Margens: Nenhuma/None
4. Escala: 100% (não 95, não 90)
5. Em "Mais configurações":
   └─ Remover cabeçalho/rodapé
```

### ❌ Problema: QR Code não escaneia

**Causas:**
- Qualidade baixa de impressão
- Tinta esgotada na Argox
- Etiqueta de má qualidade
- QR muito escuro/claro

**Solução:**
```
1. Aumentar densidade (8 > 10)
2. Aumentar temperatura (Normal > Quente)
3. Testar com novo rolo de etiqueta
4. Verificar toner da Argox
5. Limpar sensor de gap
6. Testar QR em diferentes scanners
```

### ❌ Problema: Impressora não reconhece tamanho

**Causas:**
- Driver não reconhece tamanho personalizado
- Configuração não foi salva
- Firmware da impressora desatualizado

**Solução:**
```
1. Atualizar driver Argox
2. Criar novo tamanho em: Painel > Impressoras > Propriedades
3. Reiniciar Windows
4. Testar novamente
5. Se persistir: Resetar Argox (botão de reset traseiro)
```

### ❌ Problema: Etiqueta certa, saída errada

**Causas:**
- Orientação da etiqueta invertida
- Sensor de gap desalinhado
- Software salvando configuração antiga

**Solução:**
```
1. Verificar orientação física da etiqueta
2. Limpar sensor de gap (soprar, não molhar)
3. Fazer nova configuração de tamanho
4. Reiniciar impressora (desligar/ligar)
5. Testar com página de teste nativa
```

### ❌ Problema: Dados não salvam no histórico

**Causas:**
- Banco de dados WordPress com permissões baixas
- Espaço em disco insuficiente
- Erro de conexão

**Solução:**
```
1. Verificar permissões wp-content/plugins/
2. Ativar modo de debug (wp-config.php)
3. Consultar log de erros (wp-content/debug.log)
4. Recriar tabela manualmente
5. Verificar espaço livre em disco
```

---

## 🧪 Teste de Verificação Final

Ao terminar a configuração, execute este teste:

### Teste 1: Hardware
```
[ ] Argox 2140 ligada
[ ] Etiquetas 106x52mm instaladas
[ ] Toner/Tinta adequado
[ ] Sensor de gap limpo
```

### Teste 2: Windows
```
[ ] Impressora aparece em Dispositivos
[ ] Tamanho 106x52mm configurado
[ ] Orientação Paisagem definida
[ ] Margens em 0mm
```

### Teste 3: WordPress
```
[ ] Plugin ativado
[ ] Menu QR Etiqueta visível
[ ] Formulário carrega
[ ] Código de teste: 1234567890
[ ] QR Code gerado sem erros
```

### Teste 4: Impressão
```
[ ] Abrir print via WordPress
[ ] Página mostra etiqueta 106x52mm
[ ] Pode visualizar antes
[ ] Configurações mantidas
[ ] Imprimir uma etiqueta
```

### Teste 5: Verificação Física
```
[ ] Etiqueta sai com QR visível
[ ] Tamanho está correto (106x52mm)
[ ] Qualidade aceitável
[ ] QR escaneia em leitor
[ ] Número de série legível
```

---

## 📋 Checklist Rápido - Antes de Usar em Produção

```
HARDWARE:
[ ] Argox 2140 configurada
[ ] Etiquetas 106x52mm prontas
[ ] 50+ etiquetas de teste

SOFTWARE:
[ ] WordPress instalado
[ ] Plugin QR Etiqueta ativado
[ ] Base de dados funcional
[ ] Driver Argox atualizado

CONFIGURAÇÃO:
[ ] Tamanho papel: 106x52mm
[ ] Orientação: Paisagem
[ ] Margens: 0mm
[ ] Escala: 100%
[ ] Qualidade: Máxima

TESTES:
[ ] Uma etiqueta impressa OK
[ ] QR escaneia corretamente
[ ] Número legível
[ ] Histórico salva
[ ] Pode reimprimir

PRONTO PARA USAR ✅
```

---

## 📞 Contato e Suporte

Para problemas específicos da Argox 2140:
- Manual: Procurar em arquivos da impressora
- Driver: Download no site do fabricante
- Técnico: Contato com suporte Argox

---

**Última atualização:** 9 de março de 2026
**Compatibilidade:** QR Etiqueta v1.0.0 + Argox 2140 + Windows 10/11
