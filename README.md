# 🍺 Super9 - Sistema de Controle de Bebidas para Bike Polo

Um sistema completo para gerenciamento de comandas de bebidas e placar em tempo real para eventos de Bike Polo.

## 🎯 Sobre o Projeto

O Super9 é uma aplicação web desenvolvida para facilitar o controle de bebidas durante eventos de Bike Polo. O sistema permite que participantes tenham comandas digitais com QR codes para retirar cervejas e quentão, além de acompanhar os jogos através de um placar em tempo real.

## ✨ Funcionalidades Principais

### 🍻 Controle de Comandas
- **Comandas Digitais**: Cada participante recebe uma comanda com quantidade de latas e quentão
- **QR Codes Seguros**: Geração de QR codes criptografados para validação de bebidas
- **Histórico de Consumo**: Acompanhamento completo de todas as retiradas e recargas
- **Interface Responsiva**: Funciona perfeitamente em dispositivos móveis

### 🏑 Placar em Tempo Real
- **Controle de Jogos**: Timer, gols e nomes dos times
- **Atualização Automática**: Dados sincronizados em tempo real
- **Histórico de Partidas**: Registro de todos os jogos realizados
- **Interface Visual**: Design moderno e intuitivo

### 🔧 Painel Administrativo
- **Abertura de Comandas**: Criação de novas comandas para participantes
- **Adição de Fichas**: Recarga de bebidas nas comandas existentes
- **Gerenciamento de Comandas**: Visualização e edição de todas as comandas
- **Logs do Sistema**: Monitoramento completo de todas as ações

## 🚀 Como Usar

### Para Participantes

1. **Acesse sua comanda**: Use o link personalizado com seu ID
   ```
   https://seudominio.com/super9/index.html?id=seu_id
   ```

2. **Visualize seus produtos**: Veja quantas latas e quentões você tem disponíveis

3. **Gere QR Codes**: Toque nos botões 🍺 ou 🍷 para gerar QR codes

4. **Retire suas bebidas**: Mostre o QR code para o atendimento

5. **Acompanhe o histórico**: Veja todas as suas retiradas e recargas

### Para Administradores

1. **Acesse o painel admin**: `admin.html`
2. **Abra comandas**: Crie novas comandas para participantes
3. **Adicione fichas**: Recarregue bebidas nas comandas existentes
4. **Gerencie comandas**: Visualize e edite todas as comandas
5. **Monitore logs**: Acompanhe todas as ações do sistema

### Para Atendimento

1. **Leia QR Codes**: Use o sistema de validação para ler os QR codes
2. **Confirme retiradas**: O sistema valida e registra automaticamente
3. **Visualize dados**: Veja informações do participante e histórico

## 🛠️ Tecnologias Utilizadas

- **Frontend**: HTML5, CSS3, JavaScript (Vanilla)
- **Backend**: PHP 7.4+
- **Banco de Dados**: Google Sheets (via API)
- **Autenticação**: Hash SHA-256 para QR codes
- **Libraries**: Google API Client, QRCode.js, CryptoJS

## 📁 Estrutura do Projeto

```
super9/
├── index.html              # Interface principal dos participantes
├── admin.html              # Painel administrativo
├── placar.html             # Placar em tempo real
├── gerenciar_comandas.html # Gerenciamento de comandas
├── validar.php             # Validação de QR codes
├── listar_comandas.php     # API para listar comandas
├── placar_backend.php      # Backend do placar
├── logs.php                # Sistema de logs
├── composer.json           # Dependências PHP
├── pics/                   # Fotos dos participantes
├── gifs/                   # GIFs animados por categoria
└── logs/                   # Arquivos de log do sistema
```

## ⚙️ Configuração

### Pré-requisitos
- PHP 7.4 ou superior
- Composer
- Acesso à API do Google Sheets
- Servidor web (Apache/Nginx)

### Instalação

1. **Clone o repositório**
   ```bash
   git clone [url-do-repositorio]
   cd super9
   ```

2. **Instale as dependências**
   ```bash
   composer install
   ```

3. **Configure o Google Sheets**
   - Crie um projeto no Google Cloud Console
   - Ative a Google Sheets API
   - Baixe o arquivo `credentials.json`
   - Coloque na raiz do projeto
   - ⚠️ **IMPORTANTE**: O arquivo `credentials.json` está no `.gitignore` por segurança. Nunca commite este arquivo!

4. **Configure o banco de dados**
   - Crie uma planilha no Google Sheets
   - Compartilhe com o email do service account
   - Atualize o `spreadsheetId` nos arquivos PHP

5. **Configure as permissões**
   ```bash
   chmod 755 logs/
   chmod 644 logs/*.log
   ```

## 🔐 Segurança

- **QR Codes Criptografados**: Hash SHA-256 com timestamp
- **Validação de Tempo**: QR codes expiram em 30 segundos
- **Logs Completos**: Registro de todas as ações do sistema
- **Validação de Dados**: Verificação de integridade em todas as operações
- **Arquivos Sensíveis**: `credentials.json` e logs estão protegidos no `.gitignore`

## 📊 Funcionalidades Avançadas

### Sistema de Logs
- Registro detalhado de todas as ações
- Filtros por usuário e tipo de ação
- Interface web para visualização
- Backup automático dos logs

### Placar em Tempo Real
- Timer configurável
- Controle de gols por time
- Pausa e retomada de jogos
- Histórico de partidas
- Atualização automática via AJAX

### Interface Responsiva
- Design mobile-first
- Suporte a diferentes tamanhos de tela
- Animações e transições suaves
- GIFs temáticos para feedback visual

## 🎨 Personalização

### GIFs Temáticos
O sistema inclui GIFs organizados por categoria:
- `gifs/beer/` - GIFs relacionados a cerveja
- `gifs/wine/` - GIFs relacionados a vinho/quentão
- `gifs/polo/` - GIFs de Bike Polo
- `gifs/erro/` - GIFs para mensagens de erro
- `gifs/denied/` - GIFs para acesso negado

### Fotos dos Participantes
- Armazenadas em `pics/`
- Formato: `[id_participante].png`
- Fallback: `nofoto.png`

## 🐛 Solução de Problemas

### QR Code não funciona
- Verifique se o timestamp está correto
- Confirme se o hash está sendo gerado corretamente
- Verifique a conectividade com o Google Sheets

### Placar não atualiza
- Verifique se o `placar_backend.php` está rodando
- Confirme as permissões de escrita no `placar_state.json`
- Verifique os logs do sistema

### Erro de conexão com Google Sheets
- Verifique se o `credentials.json` está correto
- Confirme se a API está ativada
- Verifique as permissões da planilha

## 📝 Logs do Sistema

O sistema gera logs detalhados em `logs/super9.log`:
- Abertura de comandas
- Validação de QR codes
- Adição de fichas
- Erros e exceções
- Ações administrativas

## 🤝 Contribuição

1. Faça um fork do projeto
2. Crie uma branch para sua feature
3. Commit suas mudanças
4. Push para a branch
5. Abra um Pull Request

## 📄 Licença

Este projeto é de uso interno para eventos de Bike Polo.

## 👨‍💻 Desenvolvido por

Sistema desenvolvido com 💻 e 🍺 para facilitar a vida dos amantes de Bike Polo!

---

**🍺 Divirta-se e beba com responsabilidade! 🏑** 