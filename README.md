# GESP - Sistema de Gestão de Estoque e Patrimônio

Sistema web desenvolvido em PHP para gestão de estoque e patrimônio, com controle de usuários, produtos, unidades, localizações e movimentações.

## Funcionalidades

- Gestão de usuários com níveis de acesso (admin e usuário comum)
- Cadastro e controle de produtos
- Gestão de unidades de medida
- Controle de localizações
- Registro de movimentações (entrada/saída)
- Relatórios e estatísticas
- Sistema de backup automático
- Interface responsiva com Bootstrap 5

## Requisitos

- PHP 7.4 ou superior
- MySQL 5.7 ou superior
- Servidor web (Apache/Nginx)
- Extensões PHP:
  - PDO
  - PDO_MySQL
  - mbstring
  - session

## Instalação

1. Clone o repositório:
```bash
git clone https://github.com/seu-usuario/gesp.git
```

2. Configure o servidor web para apontar para o diretório do projeto

3. Crie o banco de dados e importe o arquivo `database.sql`

4. Configure as credenciais do banco de dados em `includes/config.php`:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'gesp');
define('DB_USER', 'seu_usuario');
define('DB_PASS', 'sua_senha');
```

5. Ajuste as permissões dos diretórios:
```bash
chmod 775 backups/
chown apache:apache backups/
```

## Estrutura do Projeto

```
gesp/
├── includes/
│   ├── config.php      # Configurações do sistema
│   ├── functions.php   # Funções auxiliares
│   └── navbar.php      # Barra de navegação
├── pages/
│   ├── index.php       # Página inicial
│   ├── login.php       # Login
│   ├── produtos.php    # Gestão de produtos
│   ├── unidades.php    # Gestão de unidades
│   ├── localizacoes.php # Gestão de localizações
│   ├── movimentacoes.php # Registro de movimentações
│   ├── relatorios.php  # Relatórios
│   └── backup.php      # Sistema de backup
├── backups/           # Diretório de backups
└── database.sql       # Estrutura do banco de dados
```

## Segurança

- Senhas são armazenadas com hash bcrypt
- Proteção contra SQL Injection usando PDO
- Validação de sessão em todas as páginas
- Controle de acesso baseado em níveis de usuário
- Backup automático diário

## Contribuição

1. Faça um fork do projeto
2. Crie uma branch para sua feature (`git checkout -b feature/nova-feature`)
3. Commit suas mudanças (`git commit -am 'Adiciona nova feature'`)
4. Push para a branch (`git push origin feature/nova-feature`)
5. Crie um Pull Request

## Licença

Este projeto está licenciado sob a licença MIT - veja o arquivo [LICENSE](LICENSE) para detalhes.

