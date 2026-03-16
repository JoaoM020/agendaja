#!/bin/bash
echo "=========================================="
echo "Instalação da Base de Dados - Remédio Já"
echo "=========================================="

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Função para imprimir mensagens coloridas
print_status() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCESSO]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[AVISO]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERRO]${NC} $1"
}

# Verificar se MySQL está instalado
check_mysql() {
    print_status "Verificando se MySQL está instalado..."
    
    if command -v mysql &> /dev/null; then
        print_success "MySQL encontrado!"
        mysql --version
    else
        print_error "MySQL não encontrado!"
        print_status "Por favor, instale o MySQL primeiro:"
        echo "  Ubuntu/Debian: sudo apt-get install mysql-server"
        echo "  CentOS/RHEL: sudo yum install mysql-server"
        echo "  macOS: brew install mysql"
        exit 1
    fi
}

# Verificar se PHP está instalado
check_php() {
    print_status "Verificando se PHP está instalado..."
    
    if command -v php &> /dev/null; then
        print_success "PHP encontrado!"
        php --version | head -n 1
        
        # Verificar extensões PHP necessárias
        print_status "Verificando extensões PHP..."
        
        php -m | grep -q "pdo" && print_success "PDO: OK" || print_error "PDO: FALTANDO"
        php -m | grep -q "pdo_mysql" && print_success "PDO MySQL: OK" || print_error "PDO MySQL: FALTANDO"
        php -m | grep -q "mysqli" && print_success "MySQLi: OK" || print_warning "MySQLi: FALTANDO (opcional)"
        php -m | grep -q "json" && print_success "JSON: OK" || print_error "JSON: FALTANDO"
        
    else
        print_error "PHP não encontrado!"
        print_status "Por favor, instale o PHP primeiro:"
        echo "  Ubuntu/Debian: sudo apt-get install php php-mysql php-pdo"
        echo "  CentOS/RHEL: sudo yum install php php-mysql php-pdo"
        echo "  macOS: brew install php"
        exit 1
    fi
}

# Solicitar credenciais MySQL
get_mysql_credentials() {
    print_status "Configuração das credenciais MySQL..."
    
    echo -n "Host MySQL (padrão: localhost): "
    read mysql_host
    mysql_host=${mysql_host:-localhost}
    
    echo -n "Utilizador MySQL (padrão: root): "
    read mysql_user
    mysql_user=${mysql_user:-root}
    
    echo -n "Senha MySQL: "
    read -s mysql_password
    echo
    
    echo -n "Nome da base de dados (padrão: agenda_medicamentos): "
    read mysql_database
    mysql_database=${mysql_database:-agenda_medicamentos}
}

# Testar conexão MySQL
test_mysql_connection() {
    print_status "Testando conexão MySQL..."
    
    if [ -z "$mysql_password" ]; then
        mysql -h "$mysql_host" -u "$mysql_user" -e "SELECT 1;" &> /dev/null
    else
        mysql -h "$mysql_host" -u "$mysql_user" -p"$mysql_password" -e "SELECT 1;" &> /dev/null
    fi
    
    if [ $? -eq 0 ]; then
        print_success "Conexão MySQL bem-sucedida!"
    else
        print_error "Falha na conexão MySQL!"
        print_status "Verifique as credenciais e tente novamente."
        exit 1
    fi
}

# Criar base de dados
create_database() {
    print_status "Criando base de dados '$mysql_database'..."
    
    if [ -z "$mysql_password" ]; then
        mysql -h "$mysql_host" -u "$mysql_user" -e "CREATE DATABASE IF NOT EXISTS $mysql_database CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
    else
        mysql -h "$mysql_host" -u "$mysql_user" -p"$mysql_password" -e "CREATE DATABASE IF NOT EXISTS $mysql_database CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
    fi
    
    if [ $? -eq 0 ]; then
        print_success "Base de dados criada com sucesso!"
    else
        print_error "Erro ao criar base de dados!"
        exit 1
    fi
}

# Executar script SQL
execute_sql_script() {
    print_status "Executando script de criação das tabelas..."
    
    if [ ! -f "database_setup.sql" ]; then
        print_error "Ficheiro database_setup.sql não encontrado!"
        print_status "Certifique-se de que o ficheiro está no diretório atual."
        exit 1
    fi
    
    if [ -z "$mysql_password" ]; then
        mysql -h "$mysql_host" -u "$mysql_user" "$mysql_database" < database_setup.sql
    else
        mysql -h "$mysql_host" -u "$mysql_user" -p"$mysql_password" "$mysql_database" < database_setup.sql
    fi
    
    if [ $? -eq 0 ]; then
        print_success "Tabelas criadas com sucesso!"
    else
        print_error "Erro ao criar tabelas!"
        exit 1
    fi
}

# Atualizar ficheiro de configuração PHP
update_php_config() {
    print_status "Atualizando configuração PHP..."
    
    if [ -f "database_config.php" ]; then
        # Criar backup do ficheiro original
        cp database_config.php database_config.php.backup
        
        # Atualizar configurações
        sed -i "s/define('DB_HOST', 'localhost');/define('DB_HOST', '$mysql_host');/" database_config.php
        sed -i "s/define('DB_NAME', 'agenda_medicamentos');/define('DB_NAME', '$mysql_database');/" database_config.php
        sed -i "s/define('DB_USER', 'root');/define('DB_USER', '$mysql_user');/" database_config.php
        sed -i "s/define('DB_PASS', '');/define('DB_PASS', '$mysql_password');/" database_config.php
        
        print_success "Configuração PHP atualizada!"
        print_warning "Backup criado: database_config.php.backup"
    else
        print_warning "Ficheiro database_config.php não encontrado. Criando novo..."
        
        cat > database_config.php << EOF
<?php
define('DB_HOST', '$mysql_host');
define('DB_NAME', '$mysql_database');
define('DB_USER', '$mysql_user');
define('DB_PASS', '$mysql_password');
define('DB_CHARSET', 'utf8mb4');

try {
    \$pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET, DB_USER, DB_PASS);
    \$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException \$e) {
    die("Erro na conexão: " . \$e->getMessage());
}
?>
EOF
        print_success "Ficheiro database_config.php criado!"
    fi
}

# Verificar instalação
verify_installation() {
    print_status "Verificando instalação..."
    
    # Verificar se as tabelas foram criadas
    if [ -z "$mysql_password" ]; then
        table_count=$(mysql -h "$mysql_host" -u "$mysql_user" "$mysql_database" -e "SHOW TABLES;" | wc -l)
    else
        table_count=$(mysql -h "$mysql_host" -u "$mysql_user" -p"$mysql_password" "$mysql_database" -e "SHOW TABLES;" | wc -l)
    fi
    
    # Subtrair 1 porque a primeira linha é o cabeçalho
    table_count=$((table_count - 1))
    
    if [ $table_count -ge 4 ]; then
        print_success "Instalação verificada! $table_count tabelas encontradas."
    else
        print_warning "Instalação pode estar incompleta. Apenas $table_count tabelas encontradas."
    fi
}

# Criar utilizador de exemplo
create_sample_user() {
    print_status "Deseja criar um utilizador de exemplo? (s/n)"
    read -n 1 create_user
    echo
    
    if [[ $create_user =~ ^[Ss]$ ]]; then
        echo -n "Email do utilizador: "
        read user_email
        echo -n "Senha do utilizador: "
        read -s user_password
        echo
        
        # Hash da senha (usando PHP)
        password_hash=$(php -r "echo password_hash('$user_password', PASSWORD_DEFAULT);")
        
        if [ -z "$mysql_password" ]; then
            mysql -h "$mysql_host" -u "$mysql_user" "$mysql_database" -e "INSERT INTO usuarios (email, senha_hash, nome) VALUES ('$user_email', '$password_hash', 'Utilizador Exemplo');"
        else
            mysql -h "$mysql_host" -u "$mysql_user" -p"$mysql_password" "$mysql_database" -e "INSERT INTO usuarios (email, senha_hash, nome) VALUES ('$user_email', '$password_hash', 'Utilizador Exemplo');"
        fi
        
        if [ $? -eq 0 ]; then
            print_success "Utilizador criado com sucesso!"
            print_status "Email: $user_email"
        else
            print_error "Erro ao criar utilizador!"
        fi
    fi
}

# Mostrar informações finais
show_final_info() {
    echo
    echo "=========================================="
    print_success "INSTALAÇÃO CONCLUÍDA!"
    echo "=========================================="
    echo
    print_status "Informações da instalação:"
    echo "  Host: $mysql_host"
    echo "  Base de dados: $mysql_database"
    echo "  Utilizador: $mysql_user"
    echo
    print_status "Ficheiros criados/atualizados:"
    echo "  - database_config.php (configuração PHP)"
    echo "  - database_setup.sql (script de criação)"
    echo "  - database_queries.sql (consultas úteis)"
    echo
    print_status "Próximos passos:"
    echo "  1. Copie os ficheiros PHP para o seu servidor web"
    echo "  2. Certifique-se de que o PHP pode aceder à base de dados"
    echo "  3. Teste a aplicação no navegador"
    echo
    print_warning "IMPORTANTE: Mantenha as credenciais da base de dados seguras!"
    echo
}

# Função principal
main() {
    echo
    print_status "Iniciando instalação da base de dados..."
    echo
    
    # Verificações iniciais
    check_mysql
    check_php
    
    echo
    
    # Configuração
    get_mysql_credentials
    test_mysql_connection
    
    echo
    
    # Instalação
    create_database
    execute_sql_script
    update_php_config
    
    echo
    
    # Verificação
    verify_installation
    create_sample_user
    
    # Informações finais
    show_final_info
}

# Verificar se o script está a ser executado diretamente
if [[ "${BASH_SOURCE[0]}" == "${0}" ]]; then
    main "$@"
fi

