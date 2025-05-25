URLDEV = 'http://localhost:5555'
URLPROD = 'https://exapmle.com'

HOSTS = '/etc/hosts'

#где искать внешние модули?
EXTERNAL_MODULE = "../"
#Где находяться файлы миграций?
MIGRATE_DIR = '../server/migration'
DEV = 1

#Внутрении директории
DIST = 'dist'
APP = 'APP'
PUBLIC_DIR = "[ROOT][DS][DIST]"
SVG = '[PUBLIC_DIR][DS]view/img'
IMG_RELAT = "../../../view/img/"
UPLOADS = "../../../view/uploads"

#EXPORT MODULE PHP
#EXTERNAL_MODULE = "../Classes||../Api"
EXTERNAL_MODULE = "../"

#FTP
FTP_DIR_EXEPTION =
FTP_FILE_EXEPTION =
FTP_HOST_DIR =
FTP_HOST =
FTP_LOGIN =
FTP_PASSWORD = 

LOG = '../server/log/pet_cabinet.log'
#APACHE
DIR_APACHE = 
DIR_APACHE_SITE_ENBE =

#WEBPACK
JS = "view/assets/js/[name][hash].js"
CSS = "./view/assets/css/[name][hash].css"
#для clear множество разделитель ||
CLEAR = 'view/assets/**'
IMG = "view/assets/img"
FONT = "view/assets/fonts"
TEMPLATE = './head.php'

# Какие настройки подключения к базе?
DB_TYPE = mysql
DB_HOST = localhost
DB_USER = root
DB_PORT = 3306
DB_NAME = 
DB_PASSWORD = 

# Добавь соль в пароли
SALT = roiuerhvu4738