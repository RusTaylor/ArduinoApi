<p><strong>Способ установки номер 1 на Линуксе + Докер</strong></p>

В первую очередь удалите и создайте заново папку postgres которая лежит в папке docker

<ul>
<li>Установите докер(В консоли sudo apt install docker)</li>
<li>Установите докер композ(В консоли sudo apt install docker-compose)</li>
</ul>
<p>После этого перейдите в дерикторию проекта и напишите следующие команды в этом же порядке</p>
<ul>
<li>docker-compose build</li>
<li>docker-compose up -d</li>
</ul>

Когда поднимите проект вам нужно выполнить в консоли эту команду
<p><strong>docker exec -it arduinoapi_arduino_php7_1 bash</strong></p>

Вы попадёте в контейнер пхп

Теперь вам надо выполнить следующую команду <strong>php artisan migrate</strong>

После чего пишите в консоли <strong>exit</strong> нажимете клавишу enter и тем самым выходите из консоли контейнера

Проект поднят, чтобы его выключить напишите в папке с проектом <strong>docker-compose down</strong>

<p><strong>Способ установки на Linux без докера</strong></p>

Установите базу данных, nginx, php7-fpm
<p>Введите следующие команды в консоли</p>
<ul>
<li>sudo apt install nginx</li>
<li>sudo apt install postgresql</li>
<li>sudo apt install php7.4-fpm</li>
</ul>

В этом случае вам нужно поменять значение переменной "dsn" в Файле Api.php в самом верху
<p>Вставте туда эту строку <strong>pgsql:host=localhost;dbname=api</strong></p>

Потом надо взять файл nginx.conf из папки проекта и переместить в /etc/nginx/sites-enabled предварительно поменяв параметр 
<strong>root</strong>, вставте туда свой путь до проекта. Например проект лежит в /var/www/ArduinoApi, то надо указать такой путь

Потом в файле Api.php в строчке 18 замените rustaylor на root а пароль сделайте пустым, вот так '', просто 2 ковычки

Теперь в папке проекта в файле migrate напишите данные базы, а именно:
<p>Все данные пишите внутри одинарных скобочек, вот таких ' '</p>
<ul>
<li>host(если на том же компе что и проект, то пишите localhost, если в докере, то arduino_postgres)</li>
<li>dbname(название базы данных)</li>
<li>username(имя пользователя базы данных)</li>
<li>password(пароль пользователя базы данных, может быть пустым)</li>
</ul>

После чего выполните команду в консоли <strong>php migrate.php</strong>

<p><strong>Установка на Windows 10</strong></p>

Установите OpenServer. Вот ссылка на установку и настройку(https://timeweb.com/ru/community/articles/ustanovka-i-nastroyka-openserver),
там надо выбрать как веб-сервер nginx, версию пхп 7.*, т.е первая цифра 7, а после неё не важно
и базу данных postgresql

Положите проект в папку "Место установки OpenServer"/domains и запустите OpenServer

Вам нужно поменять значение переменной "dsn" в Файле Api.php в самом верху
<p>Вставте туда эту строку <strong>pgsql:host=localhost;dbname=api</strong></p>
Потом в файле Api.php в строчке 18 замените rustaylor на root а пароль сделайте пустым, вот так '', просто 2 ковычки


За точными деталями или решением ошибок пишите автору проекта

<p><strong>Описание таблиц</strong></p>

arduino_name - таблица с уникальными именами Ардуинок

arduino_param - таблица с параметрами мониторинга ардуинок

arduino_control_param - таблица с параметрами для изменения и отправки ардуинке