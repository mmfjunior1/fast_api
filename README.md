# fast_api
Pequena ferramenta, mas bastante funcional, para construção de APIs RestFul.
Conta com https://github.com/mmfjunior1/fast-api-core.git, que possui algumas ferramentas como QueryBuilder, 
sistema de roteamento, entre outras.
# Instalação
$ composer install
# VHost - Apache
<VirtualHost *:8084>
        DocumentRoot /var/www/html/fast_api/public
        ErrorLog ${APACHE_LOG_DIR}/error.log
        CustomLog ${APACHE_LOG_DIR}/access.log combined

        <Directory "/var/www/html/fast_api/public">
                AllowOverride all
                Require all granted
        </Directory>
</VirtualHost>


# Manual
Leia o manual presente neste repositório e entenda como funciona.
# Contribuição
Contribua :)
