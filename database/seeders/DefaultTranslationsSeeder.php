<?php

namespace Database\Seeders;

use App\Models\Translation;
use Illuminate\Database\Seeder;

class DefaultTranslationsSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->items() as $key => $values) {
            Translation::updateOrCreate(
                ['key' => $key],
                ['value' => $this->normalize($key, $values)]
            );

            Translation::forgetCache($key);
        }
    }

    /**
     * @param  array<string, string>  $values
     * @return array<string, string>
     */
    public function normalize(string $key, array $values): array
    {
        $locales = config('site.locales', ['ru']);
        $default = config('site.default_locale', $locales[0] ?? 'ru');
        $value = [];

        foreach ($locales as $locale) {
            $value[$locale] = $values[$locale] ?? $values[$default] ?? $key;
        }

        return $value;
    }

    /**
     * @return array<string, array<string, string>>
     */
    public function items(): array
    {
        return [
            'header-home' => [
                'en' => 'Home',
                'lv' => 'Sākums',
                'ru' => 'Главная',
            ],
            'header-about' => [
                'en' => 'About us',
                'lv' => 'Par mums',
                'ru' => 'О нас',
            ],
            'header-menu' => [
                'en' => 'Menu',
                'lv' => 'Ēdienkarte',
                'ru' => 'Меню',
            ],
            'header-contacts' => [
                'en' => 'Contacts',
                'lv' => 'Kontakti',
                'ru' => 'Контакты',
            ],
            'header-login' => [
                'en' => 'Login',
                'lv' => 'Ieiet',
                'ru' => 'Войти',
            ],
            'header-logout' => [
                'en' => 'Logout',
                'lv' => 'Iziet',
                'ru' => 'Выйти',
            ],
            'header-admin' => [
                'en' => 'Admin Panel',
                'lv' => 'Administratora panelis',
                'ru' => 'Панель администратора',
            ],
            'header-staff' => [
                'en' => 'Employee panel',
                'lv' => 'Darbinieku panelis',
                'ru' => 'Панель работника',
            ],
            'header-client' => [
                'en' => 'Personal account',
                'lv' => 'Personīgais konts',
                'ru' => 'Личный кабинет',
            ],
            'admin-header-dashboard' => [
                'en' => 'Dashboard',
                'lv' => 'Panelis',
                'ru' => 'Дашборд',
            ],
            'admin-header-users' => [
                'en' => 'Users',
                'lv' => 'Lietotāji',
                'ru' => 'Пользователи',
            ],
            'admin-header-tables' => [
                'en' => 'Tables',
                'lv' => 'Galdi',
                'ru' => 'Столики',
            ],
            'admin-header-translations' => [
                'en' => 'Translations',
                'lv' => 'Tulkojumi',
                'ru' => 'Переводы',
            ],
            'admin-header-logs' => [
                'en' => 'Logs',
                'lv' => 'Žurnāli',
                'ru' => 'Логи',
            ],
            'admin-header-site' => [
                'en' => 'To site',
                'lv' => 'Uz vietni',
                'ru' => 'На сайт',
            ],
            'footer-rights' => [
                'en' => 'All rights reserved.',
                'lv' => 'Visas tiesības aizsargātas.',
                'ru' => 'Все права защищены.',
            ],
            'perigee-name' => [
                'en' => 'PERIGEE',
                'lv' => 'PERIGEE',
                'ru' => 'PERIGEE',
            ],
            'perigee-rest-name' => [
                'en' => 'LOUNGE',
                'lv' => 'LOUNGE',
                'ru' => 'LOUNGE',
            ],
            'home-page-title' => [
                'en' => 'Low light, slow smoke and a calm soundtrack. Step out of the city\'s orbit for an evening.',
                'lv' => 'Maiga gaisma, lēni dūmi un mierīga mūzika. Izkāpiet no pilsētas orbītas uz vienu vakaru.',
                'ru' => 'Мягкий свет, неспешный дым и спокойная музыка. Сойдите с городской орбиты на один вечер.',
            ],
            'perigee-booking-button' => [
                'en' => 'Book a table',
                'lv' => 'Rezervēt galdiņu',
                'ru' => 'Забронировать стол',
            ],
            'perigee-menu-button' => [
                'en' => 'Check menu',
                'lv' => 'Skatīt ēdienkarti',
                'ru' => 'Посмотреть меню',
            ],
            'home-hero-title' => [
                'en' => 'The closest point to quiet.',
                'lv' => 'Tuvākais punkts klusumam.',
                'ru' => 'Ближайшая точка к тишине.',
            ],
            'home-feature-title-1' => [
                'en' => 'Blended in-house',
                'lv' => 'Gatavots uz vietas',
                'ru' => 'Забиваем под заказ',
            ],
            'home-feature-content-1' => [
                'en' => 'Each bowl is mixed to order, from light citrus to deep, spiced blends.',
                'lv' => 'Katra ūdenspīpe tiek gatavota pēc pasūtījuma, no vieglām citrusaugļu līdz bagātīgām garšvielu notēm.',
                'ru' => 'Каждая чаша готовится под заказ: от лёгких цитрусовых до глубоких пряных миксов.',
            ],
            'home-feature-title-2' => [
                'en' => 'Built for long evenings',
                'lv' => 'Gariem vakariem',
                'ru' => 'Для долгих вечеров',
            ],
            'home-feature-content-2' => [
                'en' => 'Deep seating, soft light and music you can still talk over.',
                'lv' => 'Ērti dīvāni, maiga gaisma un mūzika, kas netraucē sarunai.',
                'ru' => 'Удобные диваны, мягкий свет и музыка, которая не мешает разговору.',
            ],
            'home-feature-title-3' => [
                'en' => 'Book and order online',
                'lv' => 'Rezervē un pasūti tiešsaistē',
                'ru' => 'Бронь и заказ онлайн',
            ],
            'home-feature-content-3' => [
                'en' => 'Reserve a table, scan the QR code at your table to order, and earn loyalty points.',
                'lv' => 'Rezervē galdiņu, noskenē QR kodu pie galda, lai pasūtītu, un krāj lojalitātes punktus.',
                'ru' => 'Забронируйте стол, закажите по QR-коду на столе и копите баллы лояльности.',
            ],
            'about-title' => [
                'en' => 'Perigee is the point in an orbit that comes closest. That is the idea of the room: good company, close together, and no reason to hurry.',
                'lv' => 'Perigejs ir orbītas tuvākais punkts. Tāda ir arī šīs telpas ideja: labi draugi, tuvu kopā un nekur nav jāsteidzas.',
                'ru' => 'Перигей — ближайшая точка орбиты. В этом и идея зала: хорошая компания, все рядом и некуда спешить.',
            ],
            'about-section-title-1' => [
                'en' => 'The room',
                'lv' => 'Telpa',
                'ru' => 'Зал',
            ],
            'about-section-title-2' => [
                'en' => 'The menu',
                'lv' => 'Ēdienkarte',
                'ru' => 'Меню',
            ],
            'about-section-title-3' => [
                'en' => 'Online',
                'lv' => 'Tiešsaistē',
                'ru' => 'Онлайн',
            ],
            'about-section-title-4' => [
                'en' => 'House rules',
                'lv' => 'Noteikumi',
                'ru' => 'Правила',
            ],
            'about-section-content-1' => [
                'en' => 'Tables for groups, quiet corners for two, and music that stays below the conversation.',
                'lv' => 'Galdi kompānijām, klusi stūrīši diviem un mūzika, kas netraucē sarunai.',
                'ru' => 'Столы для компаний, тихие места на двоих и музыка, которая не мешает разговору.',
            ],
            'about-section-content-2' => [
                'en' => 'A short list of hookah blends, tea, cocktails and small plates, available in Russian, English and Latvian.',
                'lv' => 'Īss ūdenspīpju maisījumu, tējas, kokteiļu un uzkodu saraksts krievu, angļu un latviešu valodā.',
                'ru' => 'Короткий список кальянных миксов, чая, коктейлей и закусок на русском, английском и латышском.',
            ],
            'about-section-content-3' => [
                'en' => 'Pick a table on the floor plan, order from your phone with the QR code on the table, and collect loyalty points and promo codes in your account.',
                'lv' => 'Izvēlies galdu zāles plānā, pasūti telefonā ar QR kodu uz galda un krāj lojalitātes punktus un promo kodus savā kontā.',
                'ru' => 'Выберите стол на плане зала, заказывайте с телефона по QR-коду на столе и копите баллы и промокоды в личном кабинете.',
            ],
            'about-section-content-4' => [
                'en' => 'Be kind to other guests and to the staff, and respect the age limits for entry and smoking.',
                'lv' => 'Izturies ar cieņu pret citiem viesiem un personālu un ievēro vecuma ierobežojumus.',
                'ru' => 'Уважайте других гостей и персонал и соблюдайте возрастные ограничения.',
            ],
            'about-section-end' => [
                'en' => 'Come by for an evening.',
                'lv' => 'Ienāc uz vakaru.',
                'ru' => 'Заходите на вечер.',
            ],
            'menu-title' => [
                'en' => 'A cosmic selection of hookahs, drinks, and snacks. Find your taste and set the mood for the evening.',
                'lv' => 'Kosmiska ūdenspīpju, dzērienu un uzkodu izvēle. Atrodi savu garšu un radi vakara noskaņu.',
                'ru' => 'Космический выбор кальянов, напитков и закусок. Найди свой вкус и задай настроение вечеру.',
            ],
            'contacts-title' => [
                'en' => 'Contact us, book a table or just drop in for a visit.',
                'lv' => 'Sazinieties ar mums, rezervējiet galdiņu vai vienkārši ienāciet ciemos.',
                'ru' => 'Свяжитесь с нами, забронируйте столик или просто загляните в гости.',
            ],
            'menu-button-to-categories' => [
                'en' => 'To categories',
                'lv' => 'Uz kategorijām',
                'ru' => 'К категориям',
            ],
            'menu-button-see-product' => [
                'en' => 'Look',
                'lv' => 'Skatit',
                'ru' => 'Смотреть',
            ],
            'menu-button-close-product' => [
                'en' => 'Close',
                'lv' => 'Aizvert',
                'ru' => 'Закрыть',
            ],
            'menu-button-all-product' => [
                'en' => 'All',
                'lv' => 'Visi',
                'ru' => 'Всe',
            ],
            'menu-special-category' => [
                'en' => 'Special offers',
                'lv' => 'Ipašie piedāvājumi',
                'ru' => 'Cпецпредложения',
            ],
            'menu-unavailable' => [
                'en' => 'Unavailable',
                'lv' => 'Nav pieejams',
                'ru' => 'Временно недоступно',
            ],
            'menu-no-position-category' => [
                'en' => 'There are no items in this category yet.',
                'lv' => 'Šajā kategorijā vēl nav neviena vienuma.',
                'ru' => 'В этой категории пока нет позиций.',
            ],
            'menu-no-all-category' => [
                'en' => 'The menu is currently empty. New items will be added soon.',
                'lv' => 'Ēdienkarte pašlaik ir tukša. Drīzumā tiks pievienoti jauni vienumi.',
                'ru' => 'Меню пока пусто. Скоро здесь появятся позиции.',
            ],
            'menu-allergens-title' => [
                'en' => 'Allergens',
                'lv' => 'Alergēni',
                'ru' => 'Аллергены',
            ],
            'menu-add-to-cart' => [
                'en' => 'Add to cart',
                'lv' => 'Pievienot grozam',
                'ru' => 'Добавить в корзину',
            ],
            'menu-back' => [
                'en' => 'Back to menu',
                'lv' => 'Atpakaļ uz ēdienkarti',
                'ru' => 'Назад в меню',
            ],
            'cart-title' => [
                'en' => 'Your cart',
                'lv' => 'Jūsu grozs',
                'ru' => 'Ваша корзина',
            ],
            'cart-your-order' => [
                'en' => 'Your order',
                'lv' => 'Jūsu pasūtījums',
                'ru' => 'Ваш заказ',
            ],
            'cart-total-to-pay' => [
                'en' => 'Total to pay',
                'lv' => 'Kopā jāmaksā',
                'ru' => 'Итого к оплате',
            ],
            'cart-sum' => [
                'en' => 'Sum',
                'lv' => 'Summa',
                'ru' => 'Сумма',
            ],
            'cart-total' => [
                'en' => 'Total',
                'lv' => 'Kopā',
                'ru' => 'Итого',
            ],
            'cart-confirm' => [
                'en' => 'Confirm order',
                'lv' => 'Apstiprināt pasūtījumu',
                'ru' => 'Подтвердить заказ',
            ],
            'cart-empty' => [
                'en' => 'Cart is empty',
                'lv' => 'Grozs ir tukšs',
                'ru' => 'Корзина пуста',
            ],
            'cart-add-dishes' => [
                'en' => 'Add dishes from the menu',
                'lv' => 'Pievienot ēdienus no ēdienkartes',
                'ru' => 'Добавьте блюда из меню',
            ],
            'cart-to-menu' => [
                'en' => 'To menu',
                'lv' => 'Uz ēdienkarte',
                'ru' => 'К меню',
            ],
            'client-orders-order-again' => [
                'en' => 'Order again',
                'lv' => 'Pasūtīt vēlreiz',
                'ru' => 'Заказать ещё',
            ],
            'item-added-to-cart' => [
                'en' => 'Item added to cart',
                'lv' => 'Vienums pievienots grozam',
                'ru' => 'Товар добавлен в корзину',
            ],
            'error-already-bound-to-table' => [
                'en' => 'You are already bound to table :table. Finish your current order before ordering at another table.',
                'lv' => 'Jūs jau esat piesaistīts galdam :table. Pabeidziet pašreizējo pasūtījumu, pirms pasūtāt pie cita galda.',
                'ru' => 'Вы уже привязаны к столу :table. Закончите текущий заказ, прежде чем заказывать за другим столом.',
            ],
            'qr-table-order-title' => [
                'en' => 'Your order for this table',
                'lv' => 'Jūsu pasūtījums šim galdim',
                'ru' => 'Ваш заказ на этот стол',
            ],
            'qr-table-name-label' => [
                'en' => 'Table',
                'lv' => 'Galds',
                'ru' => 'Стол',
            ],
            'qr-table-description' => [
                'en' => 'Choose dishes from the menu and add them to your cart',
                'lv' => 'Izvēlieties ēdienus no izvēlnes un pievienojiet tos grozam',
                'ru' => 'Выберите блюда из меню и добавьте их в корзину',
            ],
            'qr-table-open-cart' => [
                'en' => 'Open cart',
                'lv' => 'Atvērt grozu',
                'ru' => 'Открыть корзину',
            ],
            'contacts-adress-title' => [
                'en' => 'Address',
                'lv' => 'Adrese',
                'ru' => 'Адрес',
            ],
            'contacts-adress-content' => [
                'en' => '12 Example Street, Exampleville',
                'lv' => '12 Example Street, Exampleville',
                'ru' => '12 Example Street, Exampleville',
            ],
            'contacts-adress-link' => [
                'en' => '#',
                'lv' => '#',
                'ru' => '#',
            ],
            'contacts-map-link' => [
                'en' => 'about:blank',
                'lv' => 'about:blank',
                'ru' => 'about:blank',
            ],
            'contacts-phone-title' => [
                'en' => 'Phone',
                'lv' => 'Talrunis',
                'ru' => 'Телефон',
            ],
            'contacts-phone-content' => [
                'en' => '+37100000000',
                'lv' => '+37100000000',
                'ru' => '+37100000000',
            ],
            'contacts-email-title' => [
                'en' => 'Email',
                'lv' => 'Epasts',
                'ru' => 'Э-почта',
            ],
            'contacts-email-content' => [
                'en' => 'hello@perigee-lounge.example',
                'lv' => 'hello@perigee-lounge.example',
                'ru' => 'hello@perigee-lounge.example',
            ],
            'contacts-social-title' => [
                'en' => 'We on social networks',
                'lv' => 'Mēs sociālajos tīklos',
                'ru' => 'Мы в соцсетях',
            ],
            'contacts-instagram-title' => [
                'en' => 'Instagram',
                'lv' => 'Instagram',
                'ru' => 'Instagram',
            ],
            'contacts-instagram-link' => [
                'en' => '#',
                'lv' => '#',
                'ru' => '#',
            ],
            'contacts-facebook-title' => [
                'en' => 'Facebook',
                'lv' => 'Facebook',
                'ru' => 'Facebook',
            ],
            'contacts-facebook-link' => [
                'en' => '#',
                'lv' => '#',
                'ru' => '#',
            ],
            'contacts-working-time-title' => [
                'en' => 'Opening hours',
                'lv' => 'Darba laiks',
                'ru' => 'Часы работы',
            ],
            'contacts-working-time-content-line-1' => [
                'en' => 'Mon–Thu: 18:00 – 00:00',
                'lv' => 'P–C: 18:00 – 00:00',
                'ru' => 'Пн–Чт: 18:00 — 00:00',
            ],
            'contacts-working-time-content-line-2' => [
                'en' => 'Fri–Sat: 18:00 – 02:00',
                'lv' => 'Pk–S: 18:00 – 02:00',
                'ru' => 'Пт–Сб: 18:00 — 02:00',
            ],
            'contacts-working-time-content-line-3' => [
                'en' => 'Sun: 18:00 – 00:00',
                'lv' => 'Sv: 18:00 – 00:00',
                'ru' => 'Вс: 18:00 — 00:00',
            ],
            'contacts-working-time-content' => [
                'en' => 'Time may change on holidays.',
                'lv' => 'Laiks svētku dienās var mainīties.',
                'ru' => 'Время может меняться в праздничные дни.',
            ],
            '404-title' => [
                'en' => 'It looks like you\'ve flown into an uncharted part of space. We don\'t have such a page, but you can return to the main page or open the menu.',
                'lv' => 'Izskatās, ka esat nokļuvis neizpētītā kosmosa daļā. Mums nav šādas lapas, bet jūs varat atgriezties galvenajā lapā vai atvērt izvēlni.',
                'ru' => 'Похоже, вы улетели в неизведанную часть космоса. Такой страницы у нас нет — но можно вернуться на главную или открыть меню.',
            ],
            '403-title' => [
                'en' => 'Access to this sector of space is restricted. Additional permissions or a different system role may be required.',
                'lv' => 'Piekļuve šim telpas sektoram ir ierobežota. Var būt nepieciešamas papildu atļaujas vai cita sistēmas loma.',
                'ru' => 'Доступ к этому сектору космоса ограничен. Возможно, нужны дополнительные права или другая роль в системе.',
            ],
            'to-main-button' => [
                'en' => 'Home',
                'lv' => 'Sākums',
                'ru' => 'На главную',
            ],
            'to-profile-button' => [
                'en' => 'To your personal account',
                'lv' => 'Uz jūsu personīgo kontu',
                'ru' => 'В личный кабинет',
            ],
            'log-in-button' => [
                'en' => 'Login',
                'lv' => 'Ieiet',
                'ru' => 'Войти',
            ],
            'log-in-title' => [
                'en' => 'Enter Perigee',
                'lv' => 'Ieej kosmoss',
                'ru' => 'Вход в Perigee',
            ],
            'log-in-content' => [
                'en' => 'Log in to your account to manage your bookings and track your bonuses.',
                'lv' => 'Piesakieties savā kontā, lai pārvaldītu savas rezervācijas un sekotu līdzi savām prēmijām.',
                'ru' => 'Войди в свой аккаунт, чтобы управлять бронями и следить за бонусами.',
            ],
            'forgot-password-title' => [
                'en' => 'Forgot password?',
                'lv' => 'Aizmirsāt paroli?',
                'ru' => 'Забыли пароль?',
            ],
            'no-account' => [
                'en' => 'Don\'t have an account?',
                'lv' => 'Nav konta?',
                'ru' => 'Нет аккаунта?',
            ],
            'register-title' => [
                'en' => 'Register',
                'lv' => 'Reģistrēties',
                'ru' => 'Зарегистрироваться',
            ],
            'register-in-title' => [
                'en' => 'Client registration',
                'lv' => 'Klienta reģistrācija',
                'ru' => 'Регистрация клиента',
            ],
            'register-in-content' => [
                'en' => 'Create an account to book tables and earn points.',
                'lv' => 'Izveidojiet kontu, lai rezervētu galdiņus un nopelnītu punktus.',
                'ru' => 'Создайте аккаунт, чтобы бронировать столики и копить бонусы.',
            ],
            'register-in-button' => [
                'en' => 'Register',
                'lv' => 'Reģistrēties',
                'ru' => 'Зарегистрироваться',
            ],
            'yes-account' => [
                'en' => 'Already have an account?',
                'lv' => 'Jau ir konts?',
                'ru' => 'Уже есть аккаунт?',
            ],
            'register-name' => [
                'en' => 'Name',
                'lv' => 'Vards',
                'ru' => 'Имя',
            ],
            'register-email' => [
                'en' => 'Email',
                'lv' => 'Epasts',
                'ru' => 'Э-почта',
            ],
            'register-password' => [
                'en' => 'Password',
                'lv' => 'Parole',
                'ru' => 'Пароль',
            ],
            'register-birth-date' => [
                'en' => 'Date of birth',
                'lv' => 'Dzimšanas datums',
                'ru' => 'Дата рождения',
            ],
            'register-birth-date-hint' => [
                'en' => 'You must be at least 20 years old to register',
                'lv' => 'Jums jābūt vismaz 20 gadus vecam, lai reģistrētos',
                'ru' => 'Для регистрации вам должно быть не менее 20 лет',
            ],
            'register-password-confirm' => [
                'en' => 'Password confirmation',
                'lv' => 'Paroles apstiprinājums',
                'ru' => 'Подтверждение пароля',
            ],
            'forgot-password-recovery' => [
                'en' => 'Access restoration',
                'lv' => 'Piekļuves atjaunošana',
                'ru' => 'Восстановление доступа',
            ],
            'forgot-password-content' => [
                'en' => 'Enter your email and we will send you a link to reset your password.',
                'lv' => 'Ievadiet savu e-pasta adresi, un mēs nosūtīsim jums saiti paroles atiestatīšanai.',
                'ru' => 'Введите email, и мы отправим ссылку для сброса пароля.',
            ],
            'recovery-button' => [
                'en' => 'Send reset link',
                'lv' => 'Sūtīt atjaunošanas saiti',
                'ru' => 'Отправить ссылку для сброса',
            ],
            'reset-password-title' => [
                'en' => 'Reset Password',
                'lv' => 'Paroles atiestatīšana',
                'ru' => 'Сброс пароля',
            ],
            'reset-password-content' => [
                'en' => 'Enter your new password below',
                'lv' => 'Ievadiet jauno paroli zemāk',
                'ru' => 'Введите новый пароль ниже',
            ],
            'confirm-password' => [
                'en' => 'Confirm Password',
                'lv' => 'Apstipriniet paroli',
                'ru' => 'Подтвердите пароль',
            ],
            'reset-password-button' => [
                'en' => 'Reset Password',
                'lv' => 'Atiestatīt paroli',
                'ru' => 'Сбросить пароль',
            ],
            'back-to-login' => [
                'en' => 'Back to Login',
                'lv' => 'Atpakaļ uz ieeju',
                'ru' => 'Назад к входу',
            ],
            'reset-password-email-content' => [
                'en' => 'We received a request to reset your password. Click the button below to set a new one.',
                'lv' => 'Mēs saņēmām pieprasījumu atiestatīt jūsu paroli. Noklikšķiniet uz pogas zemāk, lai iestatītu jaunu.',
                'ru' => 'Мы получили запрос на сброс вашего пароля. Нажмите кнопку ниже, чтобы установить новый.',
            ],
            'reset-password-email-not-requested' => [
                'en' => 'If you did not request a password reset, no further action is required.',
                'lv' => 'Ja neesat pieprasījis paroles atiestatīšanu, nekādas turpmākas darbības nav nepieciešamas.',
                'ru' => 'Если вы не запрашивали сброс пароля, никаких действий предпринимать не нужно.',
            ],
            'reset-password-email-fallback' => [
                'en' => 'If you are having trouble clicking the "Reset Password" button, copy and paste the URL below into your web browser:',
                'lv' => 'Ja rodas problēmas noklikšķinot uz pogas “Atiestatīt paroli”, nokopējiet un ielīmējiet zemāk esošo URL savā pārlūkprogrammā:',
                'ru' => 'Если у вас возникают трудности с нажатием кнопки «Сбросить пароль», скопируйте и вставьте URL ниже в адресную строку браузера:',
            ],
            'verify-email-email-content' => [
                'en' => 'Please click the button below to verify your email address.',
                'lv' => 'Lūdzu, noklikšķiniet uz pogas zemāk, lai apstiprinātu savu e-pasta adresi.',
                'ru' => 'Нажмите на кнопку ниже, чтобы подтвердить ваш email адрес.',
            ],
            'verify-email-email-not-requested' => [
                'en' => 'If you did not create an account, no further action is required.',
                'lv' => 'Ja neesat izveidojis kontu, nekādas turpmākas darbības nav nepieciešamas.',
                'ru' => 'Если вы не создавали аккаунт, никаких действий не требуется.',
            ],
            'verify-email-email-fallback' => [
                'en' => 'If you are having trouble clicking the button, copy and paste the URL below into your web browser:',
                'lv' => 'Ja rodas problēmas ar pogu, nokopējiet un ielīmējiet zemāk esošo URL savā pārlūkprogrammā:',
                'ru' => 'Если у вас проблемы с кнопкой, скопируйте и вставьте URL ниже в браузер:',
            ],
            'register-password-remember' => [
                'en' => 'Remembered your password?',
                'lv' => 'Atcerējies paroli?',
                'ru' => 'Вспомнили пароль?',
            ],
            'admin-footer-rights' => [
                'en' => 'Internal section. Do not share access with third parties.',
                'lv' => 'Iekšējā sadaļa. Nekopīgojiet piekļuvi trešajām personām.',
                'ru' => 'Внутренний раздел. Не передавайте доступ третьим лицам.',
            ],
            'admin-dashboard-title' => [
                'en' => 'Admin panel',
                'lv' => 'Administratora panelis',
                'ru' => 'Админ-панель',
            ],
            'admin-dashboard-content' => [
                'en' => 'Here you will find system management, statistics, and real-time tracking.',
                'lv' => 'Šeit atradīsiet sistēmas pārvaldību, statistiku un izsekošanu reāllaikā.',
                'ru' => 'Здесь будет управление системой, статистика, так же отслеживание в реальном времени',
            ],

            // Dashboard statistics labels
            'admin-dashboard-revenue-today' => [
                'en' => 'Revenue today',
                'lv' => 'Ienākumi šodien',
                'ru' => 'Выручка сегодня',
            ],

            'admin-dashboard-orders' => [
                'en' => 'Orders',
                'lv' => 'Pasūtījumi',
                'ru' => 'Заказы',
            ],

            'admin-dashboard-average-order-value' => [
                'en' => 'Average order value (30 days)',
                'lv' => 'Vidējā pasūtījuma vērtība (30 dienas)',
                'ru' => 'Средний чек (30 дней)',
            ],

            'admin-dashboard-now' => [
                'en' => 'Now',
                'lv' => 'Tagad',
                'ru' => 'Сейчас',
            ],

            'admin-dashboard-active-orders' => [
                'en' => 'Active orders',
                'lv' => 'Aktīvi pasūtījumi',
                'ru' => 'Активные заказы',
            ],

            'admin-dashboard-active-reservations' => [
                'en' => 'Active reservations',
                'lv' => 'Aktīvas rezervācijas',
                'ru' => 'Активные брони',
            ],

            'admin-dashboard-new-clients-30d' => [
                'en' => 'New clients (30d)',
                'lv' => 'Jauni klienti (30d)',
                'ru' => 'Новые клиенты (30д)',
            ],

            'admin-dashboard-loyalty-30d' => [
                'en' => 'Loyalty (30d)',
                'lv' => 'Lojalitāte (30d)',
                'ru' => 'Лояльность (30д)',
            ],

            'admin-dashboard-revenue-30d' => [
                'en' => 'Revenue for 30 days',
                'lv' => 'Ienākumi par 30 dienas',
                'ru' => 'Выручка за 30 дней',
            ],

            'admin-dashboard-revenue-30d-subtitle' => [
                'en' => 'By completed orders',
                'lv' => 'Pēc pabeigto pasūtījumu',
                'ru' => 'По завершённым заказам',
            ],

            'admin-dashboard-orders-30d' => [
                'en' => 'Orders for 30 days',
                'lv' => 'Pasūtījumi par 30 dienas',
                'ru' => 'Заказы за 30 дней',
            ],

            'admin-dashboard-orders-30d-subtitle' => [
                'en' => 'Number of completed',
                'lv' => 'Pabeigto skaits',
                'ru' => 'Кол-во завершённых',
            ],

            'admin-dashboard-top-promos' => [
                'en' => 'Top promo codes (30 days)',
                'lv' => 'Labākie promokodi (30 dienas)',
                'ru' => 'ТОП промокоды (30 дней)',
            ],

            'admin-dashboard-top-promos-subtitle' => [
                'en' => 'By discount amount',
                'lv' => 'Pēc atlaides summas',
                'ru' => 'По сумме скидки',
            ],

            'admin-dashboard-top-clients' => [
                'en' => 'Top clients (30 days)',
                'lv' => 'Labākie klienti (30 dienas)',
                'ru' => 'ТОП клиенты (30 дней)',
            ],

            'admin-dashboard-top-clients-subtitle' => [
                'en' => 'By order amount',
                'lv' => 'Pēc pasūtījuma summas',
                'ru' => 'По сумме заказов',
            ],

            'admin-dashboard-table-code' => [
                'en' => 'Code',
                'lv' => 'Kods',
                'ru' => 'Код',
            ],

            'admin-dashboard-table-uses' => [
                'en' => 'Uses',
                'lv' => 'Lietojumi',
                'ru' => 'Исп.',
            ],

            'admin-dashboard-table-discount' => [
                'en' => 'Discount',
                'lv' => 'Atlaide',
                'ru' => 'Скидка',
            ],

            'admin-dashboard-table-client' => [
                'en' => 'Client',
                'lv' => 'Klients',
                'ru' => 'Клиент',
            ],

            'admin-dashboard-table-email' => [
                'en' => 'Email',
                'lv' => 'E-pasts',
                'ru' => 'Email',
            ],

            'admin-dashboard-table-amount' => [
                'en' => 'Amount',
                'lv' => 'Summa',
                'ru' => 'Сумма',
            ],

            'admin-dashboard-no-data' => [
                'en' => 'No data for the last 30 days',
                'lv' => 'Nav datu par pēdējām 30 dienām',
                'ru' => 'Нет данных за последние 30 дней',
            ],

            'admin-dashboard-updated' => [
                'en' => 'Updated',
                'lv' => 'Atjaunināts',
                'ru' => 'Обновлено',
            ],

            'admin-dashboard-status' => [
                'en' => 'Status',
                'lv' => 'Statuss',
                'ru' => 'Статус',
            ],

            'admin-dashboard-discounts-30d' => [
                'en' => 'Discounts 30d',
                'lv' => 'Atlaides 30d',
                'ru' => 'Скидки 30д',
            ],

            'admin-dashboard-promo-share' => [
                'en' => 'Promo',
                'lv' => 'Promo',
                'ru' => 'Promo',
            ],

            // --- Admin: users management ---

            'admin-users-title' => [
                'en' => 'Users',
                'lv' => 'Lietotāji',
                'ru' => 'Пользователи',
            ],
            'admin-users-subtitle' => [
                'en' => 'Management of administrators, staff and clients.',
                'lv' => 'Administratoru, darbinieku un klientu pārvaldība.',
                'ru' => 'Управление администраторами, персоналом и клиентами.',
            ],

            'admin-users-create-button' => [
                'en' => 'Add',
                'lv' => 'Pievienot',
                'ru' => 'Добавить',
            ],

            'admin-users-alert-success-title' => [
                'en' => 'Success',
                'lv' => 'Veiksmīgi',
                'ru' => 'Успех',
            ],
            'admin-users-alert-error-title' => [
                'en' => 'Error',
                'lv' => 'Kļūda',
                'ru' => 'Ошибка',
            ],

            // filters
            'admin-users-filter-all' => [
                'en' => 'All',
                'lv' => 'Visi',
                'ru' => 'Все',
            ],
            'admin-users-filter-admin' => [
                'en' => 'Admins',
                'lv' => 'Administratori',
                'ru' => 'Админы',
            ],
            'admin-users-filter-staff' => [
                'en' => 'Staff',
                'lv' => 'Darbinieki',
                'ru' => 'Работники',
            ],
            'admin-users-filter-client' => [
                'en' => 'Clients',
                'lv' => 'Klienti',
                'ru' => 'Клиенты',
            ],

            // role labels
            'admin-users-role-label-admin' => [
                'en' => 'Admin',
                'lv' => 'Admins',
                'ru' => 'Админ',
            ],
            'admin-users-role-label-staff' => [
                'en' => 'Staff',
                'lv' => 'Darbinieks',
                'ru' => 'Работник',
            ],
            'admin-users-role-label-client' => [
                'en' => 'Client',
                'lv' => 'Klients',
                'ru' => 'Клиент',
            ],

            // table / list
            'admin-users-created-at-label' => [
                'en' => 'Created:',
                'lv' => 'Izveidots:',
                'ru' => 'Создан:',
            ],
            'admin-users-created-at-empty' => [
                'en' => 'Date not specified',
                'lv' => 'Datums nav norādīts',
                'ru' => 'Дата не указана',
            ],

            'admin-users-edit-button' => [
                'en' => 'Edit',
                'lv' => 'Rediģēt',
                'ru' => 'Редактировать',
            ],
            'admin-users-delete-button' => [
                'en' => 'Delete',
                'lv' => 'Dzēst',
                'ru' => 'Удалить',
            ],

            'admin-users-empty' => [
                'en' => 'No users found for the selected filter.',
                'lv' => 'Izvēlētajam filtram lietotāji nav atrasti.',
                'ru' => 'Пользователи не найдены для выбранного фильтра.',
            ],

            'admin-users-table-user' => [
                'en' => 'User',
                'lv' => 'Lietotājs',
                'ru' => 'Пользователь',
            ],
            'admin-users-table-role' => [
                'en' => 'Role',
                'lv' => 'Loma',
                'ru' => 'Роль',
            ],
            'admin-users-table-created' => [
                'en' => 'Created',
                'lv' => 'Izveidots',
                'ru' => 'Создан',
            ],
            'admin-users-table-actions' => [
                'en' => 'Actions',
                'lv' => 'Darbības',
                'ru' => 'Действия',
            ],

            // --- Admin: user show (history) ---
            'admin-users-show-title' => [
                'en' => 'User history',
                'lv' => 'Lietotāja vēsture',
                'ru' => 'История пользователя',
            ],
            'admin-users-show-viewing' => [
                'en' => 'Viewing',
                'lv' => 'Skatās',
                'ru' => 'Просмотр',
            ],
            'admin-users-show-back' => [
                'en' => 'Back to list',
                'lv' => 'Atpakaļ uz sarakstu',
                'ru' => 'Назад к списку',
            ],
            'admin-users-show-edit' => [
                'en' => 'Edit',
                'lv' => 'Rediģēt',
                'ru' => 'Редактировать',
            ],
            'admin-users-show-role' => [
                'en' => 'Role',
                'lv' => 'Loma',
                'ru' => 'Роль',
            ],
            'admin-users-show-loyalty-balance' => [
                'en' => 'Loyalty: balance',
                'lv' => 'Lojalitāte: atlikums',
                'ru' => 'Лояльность: баланс',
            ],
            'admin-users-show-loyalty-code' => [
                'en' => 'Code',
                'lv' => 'Kods',
                'ru' => 'Код',
            ],
            'admin-users-show-orders-count' => [
                'en' => 'Total orders',
                'lv' => 'Pasūtījumi kopā',
                'ru' => 'Заказов всего',
            ],
            'admin-users-show-completed-total' => [
                'en' => 'Completed amount',
                'lv' => 'Pabeigto summa',
                'ru' => 'Сумма завершённых',
            ],
            'admin-users-show-loyalty-earned' => [
                'en' => 'Loyalty earned',
                'lv' => 'Lojalitāte uzkrāta',
                'ru' => 'Лояльность начислено',
            ],
            'admin-users-show-loyalty-redeemed' => [
                'en' => 'Loyalty redeemed',
                'lv' => 'Lojalitāte iztērēta',
                'ru' => 'Лояльность списано',
            ],
            'admin-users-show-promo-claimed' => [
                'en' => 'Promo codes: claimed',
                'lv' => 'Promo kodi: saņemti',
                'ru' => 'Промокоды: получено',
            ],
            'admin-users-show-promo-used' => [
                'en' => 'Promo codes: used',
                'lv' => 'Promo kodi: izmantoti',
                'ru' => 'Промокоды: использовано',
            ],
            'admin-users-show-promo-discount-total' => [
                'en' => 'Promo discounts total',
                'lv' => 'Kopējā promo atlaide',
                'ru' => 'Скидки по промо',
            ],
            'admin-users-show-loyalty-history-title' => [
                'en' => 'Loyalty history',
                'lv' => 'Lojalitātes vēsture',
                'ru' => 'История лояльности',
            ],
            'admin-users-show-loyalty-credit' => [
                'en' => 'Credit',
                'lv' => 'Uzkrājums',
                'ru' => 'Начисление',
            ],
            'admin-users-show-loyalty-debit' => [
                'en' => 'Debit',
                'lv' => 'Iztērējums',
                'ru' => 'Списание',
            ],
            'admin-users-show-loyalty-empty' => [
                'en' => 'No records',
                'lv' => 'Nav ierakstu',
                'ru' => 'Нет записей',
            ],
            'admin-users-show-promo-claimed-title' => [
                'en' => 'User promo codes',
                'lv' => 'Lietotāja promo kodi',
                'ru' => 'Промокоды пользователя',
            ],
            'admin-users-show-promo-claimed-at' => [
                'en' => 'Claimed',
                'lv' => 'Saņemts',
                'ru' => 'Получен',
            ],
            'admin-users-show-promo-used-label' => [
                'en' => 'Used',
                'lv' => 'Izmantots',
                'ru' => 'Использован',
            ],
            'admin-users-show-promo-claimed-empty' => [
                'en' => 'No promo codes',
                'lv' => 'Nav promo kodu',
                'ru' => 'Нет промокодов',
            ],
            'admin-users-show-promo-usage-title' => [
                'en' => 'Promo code usages',
                'lv' => 'Promo kodu izmantošanas',
                'ru' => 'Использования промокодов',
            ],
            'admin-users-show-promo-usage-empty' => [
                'en' => 'No usages',
                'lv' => 'Nav izmantojumu',
                'ru' => 'Нет применений',
            ],
            'admin-users-show-promo' => [
                'en' => 'Promo',
                'lv' => 'Promo',
                'ru' => 'Промо',
            ],
            'admin-users-show-order' => [
                'en' => 'Order',
                'lv' => 'Pasūtījums',
                'ru' => 'Заказ',
            ],
            'admin-users-show-orders-title' => [
                'en' => 'User orders',
                'lv' => 'Lietotāja pasūtījumi',
                'ru' => 'Заказы пользователя',
            ],
            'admin-users-show-order-started' => [
                'en' => 'Started',
                'lv' => 'Sākts',
                'ru' => 'Начат',
            ],
            'admin-users-show-order-completed' => [
                'en' => 'Completed',
                'lv' => 'Pabeigts',
                'ru' => 'Завершён',
            ],
            'admin-users-show-order-table' => [
                'en' => 'Table',
                'lv' => 'Galds',
                'ru' => 'Стол',
            ],
            'admin-users-show-order-total' => [
                'en' => 'Total',
                'lv' => 'Kopā',
                'ru' => 'Итого',
            ],
            'admin-users-show-orders-empty' => [
                'en' => 'No orders',
                'lv' => 'Nav pasūtījumu',
                'ru' => 'Нет заказов',
            ],
            'admin-users-show-forbidden' => [
                'en' => 'Viewing is available only for clients',
                'lv' => 'Skatīšana pieejama tikai klientiem',
                'ru' => 'Просмотр доступен только для клиентов',
            ],

            // delete modal
            'admin-users-delete-modal-title' => [
                'en' => 'Confirm deletion',
                'lv' => 'Apstipriniet dzēšanu',
                'ru' => 'Подтвердите удаление',
            ],
            'admin-users-delete-modal-text-prefix' => [
                'en' => 'Do you really want to delete user',
                'lv' => 'Vai tiešām vēlaties dzēst lietotāju',
                'ru' => 'Вы действительно хотите удалить пользователя',
            ],
            'admin-users-delete-modal-text-note' => [
                'en' => 'This action cannot be undone.',
                'lv' => 'Šo darbību nevarēs atsaukt.',
                'ru' => 'Это действие нельзя будет отменить.',
            ],
            'admin-users-delete-modal-cancel' => [
                'en' => 'Cancel',
                'lv' => 'Atcelt',
                'ru' => 'Отмена',
            ],
            'admin-users-delete-modal-confirm' => [
                'en' => 'Delete',
                'lv' => 'Dzēst',
                'ru' => 'Удалить',
            ],

            // edit page
            'admin-users-edit-title' => [
                'en' => 'Edit user',
                'lv' => 'Lietotāja labošana',
                'ru' => 'Редактирование пользователя',
            ],
            'admin-users-edit-subtitle' => [
                'en' => 'Update account data and role for :email.',
                'lv' => 'Atjaunojiet konta datus un lomu lietotājam :email.',
                'ru' => 'Обновление данных аккаунта и роли для :email.',
            ],

            // common form texts
            'admin-users-form-errors-title' => [
                'en' => 'Form validation errors:',
                'lv' => 'Formas aizpildīšanas kļūdas:',
                'ru' => 'Ошибки заполнения формы:',
            ],

            'admin-users-field-name' => [
                'en' => 'Name',
                'lv' => 'Vārds',
                'ru' => 'Имя',
            ],
            'admin-users-field-email' => [
                'en' => 'Email',
                'lv' => 'E-pasts',
                'ru' => 'Email',
            ],
            'admin-users-field-role' => [
                'en' => 'Role',
                'lv' => 'Loma',
                'ru' => 'Роль',
            ],

            'admin-users-field-password-new' => [
                'en' => 'New password (optional)',
                'lv' => 'Jauna parole (nav obligāti)',
                'ru' => 'Новый пароль (необязательно)',
            ],
            'admin-users-field-password-new-placeholder' => [
                'en' => 'Leave empty if you don\'t want to change it',
                'lv' => 'Atstāj tukšu, ja nevēlaties mainīt',
                'ru' => 'Оставьте пустым, если не нужно менять',
            ],
            'admin-users-field-password-new-help' => [
                'en' => 'If the field is empty, the password will remain the same.',
                'lv' => 'Ja lauks ir tukšs, parole paliks nemainīga.',
                'ru' => 'Если поле пустое — пароль останется прежним.',
            ],

            'admin-users-save-button' => [
                'en' => 'Save',
                'lv' => 'Saglabāt',
                'ru' => 'Сохранить',
            ],
            'admin-users-cancel-button' => [
                'en' => 'Cancel',
                'lv' => 'Atcelt',
                'ru' => 'Отмена',
            ],

            // create page
            'admin-users-create-title' => [
                'en' => 'Create user',
                'lv' => 'Lietotāja izveide',
                'ru' => 'Создание пользователя',
            ],
            'admin-users-create-subtitle' => [
                'en' => 'Add an administrator, staff member or client to the PERIGEE system.',
                'lv' => 'Pievienojiet administratoru, darbinieku vai klientu PERIGEE sistēmai.',
                'ru' => 'Добавление администратора, работника или клиента в систему PERIGEE.',
            ],

            'admin-users-field-name-placeholder' => [
                'en' => 'For example: John Doe',
                'lv' => 'Piemēram: Jānis Bērziņš',
                'ru' => 'Например: Иван Иванов',
            ],
            'admin-users-field-email-placeholder' => [
                'en' => 'user@example.com',
                'lv' => 'user@example.com',
                'ru' => 'user@example.com',
            ],
            'admin-users-field-role-placeholder' => [
                'en' => 'Select role',
                'lv' => 'Izvēlieties lomu',
                'ru' => 'Выберите роль',
            ],
            'admin-users-field-password' => [
                'en' => 'Password',
                'lv' => 'Parole',
                'ru' => 'Пароль',
            ],
            'admin-users-field-password-placeholder' => [
                'en' => 'Minimum 8 characters',
                'lv' => 'Minimāli 8 rakstzīmes',
                'ru' => 'Минимум 8 символов',
            ],

            'admin-users-create-submit' => [
                'en' => 'Create',
                'lv' => 'Izveidot',
                'ru' => 'Создать',
            ],

            // flash messages / errors
            'admin-users-status-created' => [
                'en' => 'User has been created.',
                'lv' => 'Lietotājs ir izveidots.',
                'ru' => 'Пользователь создан.',
            ],
            'admin-users-status-updated' => [
                'en' => 'User has been updated.',
                'lv' => 'Lietotājs ir atjaunināts.',
                'ru' => 'Пользователь обновлён.',
            ],
            'admin-users-status-deleted' => [
                'en' => 'User has been deleted.',
                'lv' => 'Lietotājs ir dzēsts.',
                'ru' => 'Пользователь удалён.',
            ],
            'admin-users-error-self-delete' => [
                'en' => 'You cannot delete yourself.',
                'lv' => 'Jūs nevarat dzēst sevi.',
                'ru' => 'Нельзя удалить самого себя.',
            ],

            'admin-users-error-open-order' => [
                'en' => 'This client has an open order. Complete it before deleting the account.',
                'lv' => 'Šim klientam ir atvērts pasūtījums. Pabeidziet to, pirms dzēšat kontu.',
                'ru' => 'У этого клиента есть открытый заказ. Завершите его перед удалением аккаунта.',
            ],

            // --- Admin: translations management ---

            'admin-translations-title' => [
                'en' => 'Translations',
                'lv' => 'Tulkojumi',
                'ru' => 'Переводы',
            ],
            'admin-translations-subtitle' => [
                'en' => 'Manage text keys and translations for all languages.',
                'lv' => 'Teksta atslēgu un tulkojumu pārvaldība visās valodās.',
                'ru' => 'Управление текстовыми ключами и переводами на все языки.',
            ],

            'admin-translations-search-placeholder' => [
                'en' => 'Search by key or text...',
                'lv' => 'Meklēt pēc atslēgas vai teksta...',
                'ru' => 'Поиск по ключу или тексту...',
            ],

            'admin-translations-updated-at-label' => [
                'en' => 'Updated at',
                'lv' => 'Atjaunināts',
                'ru' => 'Обновлено',
            ],

            'admin-translations-default-badge' => [
                'en' => 'Default',
                'lv' => 'Noklusējums',
                'ru' => 'По умолчанию',
            ],

            'admin-translations-create-button' => [
                'en' => 'Add',
                'lv' => 'Pievienot',
                'ru' => 'Добавить',
            ],

            'admin-translations-alert-success-title' => [
                'en' => 'Success',
                'lv' => 'Veiksmīgi',
                'ru' => 'Успех',
            ],

            'admin-translations-delete-modal-title' => [
                'en' => 'Confirm deletion',
                'lv' => 'Apstipriniet dzēšanu',
                'ru' => 'Подтвердите удаление',
            ],
            'admin-translations-delete-modal-text-prefix' => [
                'en' => 'Do you really want to delete key',
                'lv' => 'Vai tiešām vēlaties dzēst atslēgu',
                'ru' => 'Вы действительно хотите удалить ключ',
            ],
            'admin-translations-delete-modal-text-note' => [
                'en' => 'This action cannot be undone.',
                'lv' => 'Šo darbību nevarēs atsaukt.',
                'ru' => 'Это действие нельзя будет отменить.',
            ],
            'admin-translations-delete-modal-cancel' => [
                'en' => 'Cancel',
                'lv' => 'Atcelt',
                'ru' => 'Отмена',
            ],
            'admin-translations-delete-modal-confirm' => [
                'en' => 'Delete',
                'lv' => 'Dzēst',
                'ru' => 'Удалить',
            ],

            // Edit page

            'admin-translations-edit-title' => [
                'en' => 'Edit translation',
                'lv' => 'Tulkojuma labošana',
                'ru' => 'Редактирование перевода',
            ],
            'admin-translations-edit-subtitle' => [
                'en' => 'Key: :key',
                'lv' => 'Atslēga: :key',
                'ru' => 'Ключ: :key',
            ],

            // Form common

            'admin-translations-form-errors-title' => [
                'en' => 'Form validation errors:',
                'lv' => 'Formas aizpildīšanas kļūdas:',
                'ru' => 'Ошибки заполнения формы:',
            ],

            'admin-translations-field-key' => [
                'en' => 'Key',
                'lv' => 'Atslēga',
                'ru' => 'Ключ',
            ],
            'admin-translations-field-text' => [
                'en' => 'Text',
                'lv' => 'Teksts',
                'ru' => 'Текст',
            ],

            'admin-translations-lang-main-label' => [
                'en' => '(default)',
                'lv' => '(noklusējuma)',
                'ru' => '(основной)',
            ],
            'admin-translations-lang-main-required' => [
                'en' => '— required language',
                'lv' => '— obligātā valoda',
                'ru' => '— обязательный язык',
            ],

            'admin-translations-save-button' => [
                'en' => 'Save',
                'lv' => 'Saglabāt',
                'ru' => 'Сохранить',
            ],
            'admin-translations-cancel-button' => [
                'en' => 'Cancel',
                'lv' => 'Atcelt',
                'ru' => 'Отмена',
            ],

            // Create page

            'admin-translations-create-title' => [
                'en' => 'Create translation',
                'lv' => 'Tulkojuma izveide',
                'ru' => 'Создание перевода',
            ],
            'admin-translations-create-subtitle' => [
                'en' => 'Add a new key and texts in all languages.',
                'lv' => 'Pievienojiet jaunu atslēgu un tekstus visās valodās.',
                'ru' => 'Добавление нового ключа и текстов на все языки.',
            ],

            'admin-translations-field-key-placeholder' => [
                'en' => 'For example: home-title',
                'lv' => 'Piemēram: home-title',
                'ru' => 'Например: home-title',
            ],

            'admin-translations-create-submit' => [
                'en' => 'Create',
                'lv' => 'Izveidot',
                'ru' => 'Создать',
            ],

            // List / table

            'admin-translations-table-key' => [
                'en' => 'Key',
                'lv' => 'Atslēga',
                'ru' => 'Ключ',
            ],
            'admin-translations-table-actions' => [
                'en' => 'Actions',
                'lv' => 'Darbības',
                'ru' => 'Действия',
            ],

            'admin-translations-edit-button' => [
                'en' => 'Edit',
                'lv' => 'Rediģēt',
                'ru' => 'Редактировать',
            ],
            'admin-translations-delete-button' => [
                'en' => 'Delete',
                'lv' => 'Dzēst',
                'ru' => 'Удалить',
            ],

            'admin-translations-delete-confirm' => [
                'en' => 'Delete translation :key?',
                'lv' => 'Dzēst tulkojumu :key?',
                'ru' => 'Удалить перевод :key?',
            ],

            'admin-translations-empty' => [
                'en' => 'No translations found.',
                'lv' => 'Tulkojumi nav atrasti.',
                'ru' => 'Переводы не найдены.',
            ],

            // Flash messages

            'admin-translations-status-created' => [
                'en' => 'Translation has been added.',
                'lv' => 'Tulkojums ir pievienots.',
                'ru' => 'Перевод добавлен.',
            ],
            'admin-translations-status-updated' => [
                'en' => 'Translation has been updated.',
                'lv' => 'Tulkojums ir atjaunināts.',
                'ru' => 'Перевод обновлён.',
            ],
            'admin-translations-status-deleted' => [
                'en' => 'Translation has been deleted.',
                'lv' => 'Tulkojums ir dzēsts.',
                'ru' => 'Перевод удалён.',
            ],

            // --- Staff area: dashboard, header, footer ---

            'staff-dashboard-title' => [
                'en' => 'Staff panel',
                'lv' => 'Personāla panelis',
                'ru' => 'Панель персонала',
            ],
            'staff-dashboard-subtitle' => [
                'en' => 'Here you can see your shifts, table reservations and guest orders (only what staff needs).',
                'lv' => 'Šeit varat skatīt savas maiņas, galdiņu rezervācijas un viesu pasūtījumus (tikai to, kas nepieciešams personālam).',
                'ru' => 'Здесь вы можете смотреть свои смены, бронь столов и заказы гостей (только то, что нужно персоналу).',
            ],
            'staff-dashboard-upcoming-title' => [
                'en' => 'Tomorrow reservations',
                'lv' => 'Rītdienas rezervācijas',
                'ru' => 'Брони на завтра',
            ],
            'staff-dashboard-upcoming-date' => [
                'en' => 'Date: :date',
                'lv' => 'Datums: :date',
                'ru' => 'Дата: :date',
            ],
            'staff-dashboard-upcoming-empty' => [
                'en' => 'No reservations for tomorrow yet.',
                'lv' => 'Rītdienai rezervāciju vēl nav.',
                'ru' => 'На завтра пока нет броней.',
            ],

            'staff-header-badge' => [
                'en' => 'Staff panel',
                'lv' => 'Personāla panelis',
                'ru' => 'Панель персонала',
            ],
            'staff-header-dashboard-link' => [
                'en' => 'Dashboard',
                'lv' => 'Panelis',
                'ru' => 'Дашборд',
            ],
            'staff-header-menu-link' => [
                'en' => 'Menu',
                'lv' => 'Ēdienkarte',
                'ru' => 'Меню',
            ],
            'staff-header-to-site-link' => [
                'en' => 'To website',
                'lv' => 'Uz saiti',
                'ru' => 'На сайт',
            ],
            'staff-header-logout' => [
                'en' => 'Logout',
                'lv' => 'Iziet',
                'ru' => 'Выход',
            ],
            'staff-header-employee-fallback' => [
                'en' => 'Staff member',
                'lv' => 'Darbinieks',
                'ru' => 'Сотрудник',
            ],

            'staff-footer-left' => [
                'en' => '© :year PERIGEE LOUNGE — Staff panel',
                'lv' => '© :year PERIGEE LOUNGE — personāla panelis',
                'ru' => '© :year PERIGEE LOUNGE — Панель персонала',
            ],
            'staff-footer-right' => [
                'en' => 'Access is restricted to venue staff only.',
                'lv' => 'Piekļuve tikai iestādes darbiniekiem.',
                'ru' => 'Доступ только для сотрудников заведения.',
            ],

            // --- Staff: menu index ---

            'staff-menu-title' => [
                'en' => 'Menu',
                'lv' => 'Ēdienkarte',
                'ru' => 'Меню',
            ],
            'staff-menu-subtitle' => [
                'en' => 'Manage menu categories and items. Drag and drop to change the order.',
                'lv' => 'Kategoriju un ēdienkartes pozīciju pārvaldība. Velciet, lai mainītu secību.',
                'ru' => 'Управление категориями и позициями меню. Перетаскивайте, чтобы менять порядок.',
            ],
            'staff-menu-add-category-button' => [
                'en' => 'Category',
                'lv' => 'Kategorija',
                'ru' => 'Категория',
            ],
            'staff-menu-category-badge-special' => [
                'en' => 'Special offer',
                'lv' => 'Īpašais piedāvājums',
                'ru' => 'Спецпредложение',
            ],
            'staff-menu-category-edit-button' => [
                'en' => 'Edit',
                'lv' => 'Rediģēt',
                'ru' => 'Редактировать',
            ],
            'staff-menu-category-delete-button' => [
                'en' => 'Delete',
                'lv' => 'Dzēst',
                'ru' => 'Удалить',
            ],
            'staff-menu-category-delete-confirm' => [
                'en' => 'Delete the category and all its items?',
                'lv' => 'Dzēst kategoriju un visas tās pozīcijas?',
                'ru' => 'Удалить категорию и все её позиции?',
            ],
            'staff-menu-category-delete-modal-title' => [
                'en' => 'Confirm deletion',
                'lv' => 'Apstipriniet dzēšanu',
                'ru' => 'Подтвердите удаление',
            ],
            'staff-menu-category-delete-modal-text-prefix' => [
                'en' => 'Delete category',
                'lv' => 'Dzēst kategoriju',
                'ru' => 'Удалить категорию',
            ],
            'staff-menu-category-delete-modal-text-note' => [
                'en' => 'All items inside will also be removed. This action cannot be undone.',
                'lv' => 'Tiks dzēstas arī visas pozīcijas. Šo darbību nevarēs atsaukt.',
                'ru' => 'Будут удалены и все позиции. Это действие нельзя отменить.',
            ],
            'staff-menu-category-delete-modal-cancel' => [
                'en' => 'Cancel',
                'lv' => 'Atcelt',
                'ru' => 'Отмена',
            ],
            'staff-menu-category-delete-modal-confirm' => [
                'en' => 'Delete',
                'lv' => 'Dzēst',
                'ru' => 'Удалить',
            ],
            'staff-menu-items-heading' => [
                'en' => 'Items',
                'lv' => 'Pozīcijas',
                'ru' => 'Позиции',
            ],
            'staff-menu-items-add-button' => [
                'en' => 'Add item',
                'lv' => 'Pievienot pozīciju',
                'ru' => 'Добавить позицию',
            ],
            'staff-menu-item-edit-button' => [
                'en' => 'Edit',
                'lv' => 'Rediģēt',
                'ru' => 'Редактировать',
            ],
            'staff-menu-item-delete-button' => [
                'en' => 'Delete',
                'lv' => 'Dzēst',
                'ru' => 'Удалить',
            ],
            'staff-menu-item-delete-confirm' => [
                'en' => 'Delete this item?',
                'lv' => 'Dzēst šo pozīciju?',
                'ru' => 'Удалить эту позицию?',
            ],
            'staff-menu-item-delete-modal-title' => [
                'en' => 'Confirm deletion',
                'lv' => 'Apstipriniet dzēšanu',
                'ru' => 'Подтвердите удаление',
            ],
            'staff-menu-item-delete-modal-text-prefix' => [
                'en' => 'Delete item',
                'lv' => 'Dzēst pozīciju',
                'ru' => 'Удалить позицию',
            ],
            'staff-menu-item-delete-modal-text-note' => [
                'en' => 'This action cannot be undone.',
                'lv' => 'Šo darbību nevarēs atsaukt.',
                'ru' => 'Это действие нельзя будет отменить.',
            ],
            'staff-menu-item-delete-modal-cancel' => [
                'en' => 'Cancel',
                'lv' => 'Atcelt',
                'ru' => 'Отмена',
            ],
            'staff-menu-item-delete-modal-confirm' => [
                'en' => 'Delete',
                'lv' => 'Dzēst',
                'ru' => 'Удалить',
            ],
            'staff-menu-category-empty-items' => [
                'en' => 'This category has no items yet.',
                'lv' => 'Šajā kategorijā pagaidām nav pozīciju.',
                'ru' => 'В этой категории пока нет позиций.',
            ],
            'staff-menu-empty-categories' => [
                'en' => 'There are no categories yet. Create the first category.',
                'lv' => 'Kategorijas vēl nav. Izveidojiet pirmo kategoriju.',
                'ru' => 'Категорий пока нет. Создайте первую категорию.',
            ],
            'staff-menu-sort-error' => [
                'en' => 'Failed to save order. Please try again.',
                'lv' => 'Kļūda saglabājot secību. Mēģiniet vēlreiz.',
                'ru' => 'Ошибка сохранения порядка. Попробуйте ещё раз.',
            ],

            // --- Staff: common form texts ---

            'staff-lang-main-label' => [
                'en' => '(default)',
                'lv' => '(pamata)',
                'ru' => '(основной)',
            ],
            'staff-lang-main-required' => [
                'en' => '— required language',
                'lv' => '— obligātā valoda',
                'ru' => '— обязательный язык',
            ],
            'staff-field-name-label' => [
                'en' => 'Name',
                'lv' => 'Nosaukums',
                'ru' => 'Название',
            ],
            'staff-field-description-label' => [
                'en' => 'Description',
                'lv' => 'Apraksts',
                'ru' => 'Описание',
            ],
            'staff-field-description-optional-label' => [
                'en' => 'Description (optional)',
                'lv' => 'Apraksts (nav obligāti)',
                'ru' => 'Описание (необязательно)',
            ],
            'staff-field-allergens-label' => [
                'en' => 'Allergens',
                'lv' => 'Alergēni',
                'ru' => 'Аллергены',
            ],
            'staff-field-allergens-help' => [
                'en' => 'For example: gluten, milk, nuts.',
                'lv' => 'Piemēram: glutēns, piens, rieksti.',
                'ru' => 'Например: глютен, молоко, орехи.',
            ],
            'staff-field-price-label' => [
                'en' => 'Price (€)',
                'lv' => 'Cena (€)',
                'ru' => 'Цена (€)',
            ],
            'staff-field-image-optional-label' => [
                'en' => 'Photo (optional)',
                'lv' => 'Foto (nav obligāti)',
                'ru' => 'Фото (необязательно)',
            ],
            'staff-field-category-label' => [
                'en' => 'Category',
                'lv' => 'Kategorija',
                'ru' => 'Категория',
            ],
            'staff-field-current-image-label' => [
                'en' => 'Current image',
                'lv' => 'Pašreizējais attēls',
                'ru' => 'Текущее изображение',
            ],
            'staff-field-image-keep-help' => [
                'en' => 'Leave the field empty if you do not want to change the image.',
                'lv' => 'Atstājiet lauku tukšu, ja nevēlaties mainīt attēlu.',
                'ru' => 'Оставьте поле пустым, если не хотите менять картинку.',
            ],
            'staff-save-button' => [
                'en' => 'Save',
                'lv' => 'Saglabāt',
                'ru' => 'Сохранить',
            ],
            'staff-save-changes-button' => [
                'en' => 'Save changes',
                'lv' => 'Saglabāt izmaiņas',
                'ru' => 'Сохранить изменения',
            ],
            'staff-back-to-menu' => [
                'en' => 'Back to orders',
                'lv' => 'Atpakaļ uz pasūtījumiem',
                'ru' => 'Назад к заказам',
            ],

            // --- Staff: category forms ---

            'staff-menu-category-create-title' => [
                'en' => 'New category',
                'lv' => 'Jauna kategorija',
                'ru' => 'Новая категория',
            ],
            'staff-menu-category-create-subtitle' => [
                'en' => 'Fill in the name and description in the required languages.',
                'lv' => 'Aizpildiet nosaukumu un aprakstu vajadzīgajās valodās.',
                'ru' => 'Заполните название и описание на нужных языках.',
            ],
            'staff-menu-category-edit-title' => [
                'en' => 'Edit category',
                'lv' => 'Kategorijas labošana',
                'ru' => 'Редактирование категории',
            ],
            'staff-menu-category-edit-subtitle' => [
                'en' => 'Change translated name and description.',
                'lv' => 'Izmainiet nosaukuma un apraksta tulkojumus.',
                'ru' => 'Измените переводы названия и описания.',
            ],
            'staff-menu-category-special-checkbox' => [
                'en' => 'This category is a special offers section',
                'lv' => 'Šī kategorija ir īpašo piedāvājumu sadaļa',
                'ru' => 'Эта категория — спецпредложения',
            ],
            'staff-menu-category-special-label' => [
                'en' => 'Special offers',
                'lv' => 'Īpaši piedāvājumi',
                'ru' => 'Спецпредложения',
            ],
            'staff-menu-category-available-label' => [
                'en' => 'Category is available',
                'lv' => 'Kategorija ir pieejama',
                'ru' => 'Категория доступна',
            ],
            'staff-menu-category-badge-unavailable' => [
                'en' => 'Unavailable',
                'lv' => 'Nav pieejams',
                'ru' => 'Недоступно',
            ],
            'staff-menu-item-available-label' => [
                'en' => 'Item is available',
                'lv' => 'Pozīcija ir pieejama',
                'ru' => 'Позиция доступна',
            ],
            'staff-menu-item-badge-unavailable' => [
                'en' => 'Unavailable',
                'lv' => 'Nav pieejams',
                'ru' => 'Недоступно',
            ],

            // --- Staff: item forms ---

            'staff-menu-item-create-title' => [
                'en' => 'New item — :category',
                'lv' => 'Jauna pozīcija — :category',
                'ru' => 'Новая позиция — :category',
            ],
            'staff-menu-item-create-subtitle' => [
                'en' => 'Fill in the name and description in the required languages.',
                'lv' => 'Aizpildiet nosaukumu un aprakstu vajadzīgajās valodās.',
                'ru' => 'Заполните название и описание на нужных языках.',
            ],
            'staff-menu-item-edit-title' => [
                'en' => 'Edit item',
                'lv' => 'Pozīcijas labošana',
                'ru' => 'Редактирование позиции',
            ],
            'staff-menu-item-edit-subtitle' => [
                'en' => 'Update translations, price and image.',
                'lv' => 'Atjauniniet tulkojumus, cenu un attēlu.',
                'ru' => 'Обновите переводы, цену и изображение.',
            ],

            // --- Staff: flash messages ---

            'staff-menu-category-created' => [
                'en' => 'Category created.',
                'lv' => 'Kategorija izveidota.',
                'ru' => 'Категория создана.',
            ],
            'staff-menu-category-updated' => [
                'en' => 'Category updated.',
                'lv' => 'Kategorija atjaunināta.',
                'ru' => 'Категория обновлена.',
            ],
            'staff-menu-category-deleted' => [
                'en' => 'Category deleted.',
                'lv' => 'Kategorija dzēsta.',
                'ru' => 'Категория удалена.',
            ],
            'staff-menu-item-created' => [
                'en' => 'Item added.',
                'lv' => 'Pozīcija pievienota.',
                'ru' => 'Позиция добавлена.',
            ],
            'staff-menu-item-updated' => [
                'en' => 'Item updated.',
                'lv' => 'Pozīcija atjaunināta.',
                'ru' => 'Позиция обновлена.',
            ],
            'staff-menu-item-deleted' => [
                'en' => 'Item deleted.',
                'lv' => 'Pozīcija dzēsta.',
                'ru' => 'Позиция удалена.',
            ],

            'staff-menu-item-delete-blocked' => [
                'en' => 'This item appears in orders, so it cannot be deleted. Hide it instead.',
                'lv' => 'Šī pozīcija ir pasūtījumos, tāpēc to nevar dzēst. Tā vietā paslēpiet to.',
                'ru' => 'Эта позиция есть в заказах, поэтому её нельзя удалить. Вместо этого скройте её.',
            ],

            'staff-menu-category-delete-blocked' => [
                'en' => 'This category has items that appear in orders, so it cannot be deleted. Hide it instead.',
                'lv' => 'Šajā kategorijā ir pozīcijas, kas ir pasūtījumos, tāpēc to nevar dzēst. Tā vietā paslēpiet to.',
                'ru' => 'В этой категории есть позиции из заказов, поэтому её нельзя удалить. Вместо этого скройте её.',
            ],

            // --- Client area: dashboard ---

            'client-dashboard-badge' => [
                'en' => 'Client panel',
                'lv' => 'Klienta panelis',
                'ru' => 'Панель клиента',
            ],
            'client-dashboard-title' => [
                'en' => 'Welcome, :name',
                'lv' => 'Laipni lūgts, :name',
                'ru' => 'Добро пожаловать, :name',
            ],
            'client-dashboard-subtitle' => [
                'en' => 'Here you can see your shifts, table reservations and active guest orders.',
                'lv' => 'Šeit varat skatīt savas maiņas, galdiņu rezervācijas un aktīvos viesu pasūtījumus.',
                'ru' => 'Здесь вы можете смотреть свои смены, бронь столов и активные заказы гостей.',
            ],

            'client-dashboard-reservations-title' => [
                'en' => 'Table reservations',
                'lv' => 'Galdiņu rezervācijas',
                'ru' => 'Брони столов',
            ],
            'client-dashboard-reservations-text' => [
                'en' => 'List of reservations for the near future.',
                'lv' => 'Rezervāciju saraksts tuvākajam laikam.',
                'ru' => 'Список броней на ближайшее время.',
            ],
            'client-dashboard-orders-title' => [
                'en' => 'Active orders',
                'lv' => 'Aktīvie pasūtījumi',
                'ru' => 'Активные заказы',
            ],
            'client-dashboard-orders-text' => [
                'en' => 'Orders that need to be served.',
                'lv' => 'Pasūtījumi, kas jāapkalpo.',
                'ru' => 'Заказы, которые нужно обслужить.',
            ],

            // --- Client area: sidebar ---

            'client-sidebar-title' => [
                'en' => 'Navigation',
                'lv' => 'Navigācija',
                'ru' => 'Навигация',
            ],
            'client-sidebar-overview' => [
                'en' => 'Overview',
                'lv' => 'Pārskats',
                'ru' => 'Обзор',
            ],
            'client-sidebar-profile' => [
                'en' => 'Profile settings',
                'lv' => 'Profila iestatījumi',
                'ru' => 'Настройки профиля',
            ],
            'client-sidebar-reservations' => [
                'en' => 'Table reservations',
                'lv' => 'Galdiņu rezervācijas',
                'ru' => 'Брони столов',
            ],
            'client-sidebar-orders' => [
                'en' => 'Orders',
                'lv' => 'Pasūtījumi',
                'ru' => 'Заказы',
            ],

            // --- Client area: profile header ---

            'client-profile-badge' => [
                'en' => 'Client profile',
                'lv' => 'Klienta profils',
                'ru' => 'Профиль клиента',
            ],
            'client-profile-title' => [
                'en' => 'Profile settings',
                'lv' => 'Profila iestatījumi',
                'ru' => 'Настройки профиля',
            ],
            'client-profile-subtitle' => [
                'en' => 'Here you can change your account details and password.',
                'lv' => 'Šeit varat mainīt sava konta datus un paroli.',
                'ru' => 'Здесь вы можете изменить свои данные аккаунта и пароль.',
            ],

            // --- Client area: profile data form ---

            'client-profile-section-data-title' => [
                'en' => 'Profile data',
                'lv' => 'Profila dati',
                'ru' => 'Данные профиля',
            ],
            'client-profile-name-label' => [
                'en' => 'Name',
                'lv' => 'Vārds',
                'ru' => 'Имя',
            ],
            'client-profile-email-label' => [
                'en' => 'Email',
                'lv' => 'E-pasts',
                'ru' => 'Email',
            ],
            'client-profile-email-unverified' => [
                'en' => 'Your email is not verified.',
                'lv' => 'Jūsu e-pasts nav apstiprināts.',
                'ru' => 'Ваш email не подтверждён.',
            ],
            'client-profile-save-button' => [
                'en' => 'Save',
                'lv' => 'Saglabāt',
                'ru' => 'Сохранить',
            ],

            // --- Client area: password form ---

            'client-profile-password-section-title' => [
                'en' => 'Change password',
                'lv' => 'Paroles maiņa',
                'ru' => 'Смена пароля',
            ],
            'client-profile-current-password-label' => [
                'en' => 'Current password',
                'lv' => 'Pašreizējā parole',
                'ru' => 'Текущий пароль',
            ],
            'client-profile-new-password-label' => [
                'en' => 'New password',
                'lv' => 'Jaunā parole',
                'ru' => 'Новый пароль',
            ],
            'client-profile-new-password-confirm-label' => [
                'en' => 'Repeat new password',
                'lv' => 'Atkārtojiet jauno paroli',
                'ru' => 'Повторите новый пароль',
            ],
            'client-profile-password-button' => [
                'en' => 'Update password',
                'lv' => 'Atjaunināt paroli',
                'ru' => 'Обновить пароль',
            ],

            // --- Client area: flash messages ---

            'client-profile-updated' => [
                'en' => 'Profile updated.',
                'lv' => 'Profils atjaunināts.',
                'ru' => 'Профиль обновлён.',
            ],
            'client-password-updated' => [
                'en' => 'Password updated.',
                'lv' => 'Parole atjaunināta.',
                'ru' => 'Пароль обновлён.',
            ],

            // --- Client area: (optional) danger zone ---

            'client-profile-danger-title' => [
                'en' => 'Danger zone',
                'lv' => 'Bīstamā zona',
                'ru' => 'Опасная зона',
            ],
            'client-profile-danger-text' => [
                'en' => 'Deleting your account is irreversible. All data will be lost.',
                'lv' => 'Konta dzēšana ir neatgriezeniska. Visi dati tiks zaudēti.',
                'ru' => 'Удаление аккаунта необратимо. Все данные будут потеряны.',
            ],
            'client-profile-danger-confirm' => [
                'en' => 'Really delete the account?',
                'lv' => 'Tiešām dzēst kontu?',
                'ru' => 'Точно удалить аккаунт?',
            ],
            'client-profile-danger-button' => [
                'en' => 'Delete account',
                'lv' => 'Dzēst kontu',
                'ru' => 'Удалить аккаунт',
            ],

            'admin-tables-title' => [
                'en' => 'Tables and hall layout',
                'lv' => 'Galdiņi un zāles plāns',
                'ru' => 'Столы и план зала',
            ],
            'admin-tables-subtitle' => [
                'en' => 'Manage tables, capacity and hall layout.',
                'lv' => 'Pārvaldi galdiņus, vietu skaitu un zāles plānu.',
                'ru' => 'Управляйте столами, количеством мест и планом зала.',
            ],
            'admin-tables-create-button' => [
                'en' => 'Add table',
                'lv' => 'Pievienot galdiņu',
                'ru' => 'Добавить стол',
            ],
            'admin-tables-alert-success-title' => [
                'en' => 'Tables updated',
                'lv' => 'Galdiņi atjaunoti',
                'ru' => 'Столы обновлены',
            ],
            'admin-tables-plan-title' => [
                'en' => 'Hall plan (top view)',
                'lv' => 'Zāles plāns (skats no augšas)',
                'ru' => 'План зала (вид сверху)',
            ],
            'admin-tables-save-layout-button' => [
                'en' => 'Save layout',
                'lv' => 'Saglabāt plānu',
                'ru' => 'Сохранить план',
            ],
            'admin-tables-plan-help' => [
                'en' => 'Drag tables to arrange the hall layout. Changes are visible to staff and clients.',
                'lv' => 'Velc galdiņus, lai sakārtotu zāles plānu. Izmaiņas redzēs gan darbinieki, gan klienti.',
                'ru' => 'Перетаскивайте столы, чтобы настроить план зала. Изменения увидят сотрудники и клиенты.',
            ],
            'admin-tables-list-title' => [
                'en' => 'Tables list',
                'lv' => 'Galdiņu saraksts',
                'ru' => 'Список столов',
            ],
            'admin-tables-capacity-label' => [
                'en' => 'Seats',
                'lv' => 'Vietas',
                'ru' => 'Мест',
            ],
            'admin-tables-status-active' => [
                'en' => 'Active',
                'lv' => 'Aktīvs',
                'ru' => 'Активен',
            ],
            'admin-tables-status-inactive' => [
                'en' => 'Inactive',
                'lv' => 'Neaktīvs',
                'ru' => 'Неактивен',
            ],
            'admin-tables-edit-button' => [
                'en' => 'Edit',
                'lv' => 'Rediģēt',
                'ru' => 'Редактировать',
            ],
            'admin-tables-delete-button' => [
                'en' => 'Delete',
                'lv' => 'Dzēst',
                'ru' => 'Удалить',
            ],
            'admin-tables-delete-confirm' => [
                'en' => 'Delete this table? Reservations history will be lost.',
                'lv' => 'Dzēst šo galdiņu? Rezervāciju vēsture tiks zaudēta.',
                'ru' => 'Удалить этот стол? История бронирований будет потеряна.',
            ],

            'admin-tables-delete-modal-title' => [
                'en' => 'Confirm deletion',
                'lv' => 'Apstipriniet dzēšanu',
                'ru' => 'Подтвердите удаление',
            ],
            'admin-tables-delete-modal-text-prefix' => [
                'en' => 'Do you really want to delete table',
                'lv' => 'Vai tiešām vēlaties dzēst galdiņu',
                'ru' => 'Вы действительно хотите удалить стол',
            ],
            'admin-tables-delete-modal-text-note' => [
                'en' => 'Reservations history will be lost. This action cannot be undone.',
                'lv' => 'Rezervāciju vēsture tiks zaudēta. Šo darbību nevarēs atsaukt.',
                'ru' => 'История бронирований будет потеряна. Это действие нельзя отменить.',
            ],
            'admin-tables-delete-modal-cancel' => [
                'en' => 'Cancel',
                'lv' => 'Atcelt',
                'ru' => 'Отмена',
            ],
            'admin-tables-delete-modal-confirm' => [
                'en' => 'Delete',
                'lv' => 'Dzēst',
                'ru' => 'Удалить',
            ],
            'admin-tables-empty' => [
                'en' => 'No tables yet. Add at least one table to build the layout.',
                'lv' => 'Pagaidām nav neviena galdiņa. Pievieno vismaz vienu, lai izveidotu plānu.',
                'ru' => 'Пока нет ни одного стола. Добавьте хотя бы один, чтобы построить план.',
            ],
            'admin-tables-save-layout-success' => [
                'en' => 'Layout saved.',
                'lv' => 'Plāns saglabāts.',
                'ru' => 'План сохранён.',
            ],
            'admin-tables-save-layout-error' => [
                'en' => 'Error while saving layout. Try again.',
                'lv' => 'Kļūda, saglabājot plānu. Mēģini vēlreiz.',
                'ru' => 'Ошибка при сохранении плана. Попробуйте ещё раз.',
            ],
            'admin-tables-created' => [
                'en' => 'Table created.',
                'lv' => 'Galdiņš izveidots.',
                'ru' => 'Стол создан.',
            ],
            'admin-tables-updated' => [
                'en' => 'Table updated.',
                'lv' => 'Galdiņš atjaunots.',
                'ru' => 'Стол обновлён.',
            ],
            'admin-tables-deleted' => [
                'en' => 'Table deleted.',
                'lv' => 'Galdiņš izdzēsts.',
                'ru' => 'Стол удалён.',
            ],
            'admin-tables-create-title' => [
                'en' => 'Create table',
                'lv' => 'Izveidot galdiņu',
                'ru' => 'Создать стол',
            ],
            'admin-tables-create-subtitle' => [
                'en' => 'Set the name, capacity and basic position of the table.',
                'lv' => 'Norādi nosaukumu, vietu skaitu un sākotnējo galdiņa novietojumu.',
                'ru' => 'Укажите название, количество мест и начальное расположение стола.',
            ],
            'admin-tables-form-errors-title' => [
                'en' => 'Please check the form fields.',
                'lv' => 'Lūdzu, pārbaudi formas laukus.',
                'ru' => 'Проверьте поля формы.',
            ],
            'admin-tables-field-name' => [
                'en' => 'Table name',
                'lv' => 'Galdiņa nosaukums',
                'ru' => 'Название стола',
            ],
            'admin-tables-field-capacity' => [
                'en' => 'Capacity (guests)',
                'lv' => 'Vietu skaits (viesi)',
                'ru' => 'Количество мест (гостей)',
            ],
            'admin-tables-field-pos-x' => [
                'en' => 'Position X (%)',
                'lv' => 'X pozīcija (%)',
                'ru' => 'Позиция X (%)',
            ],
            'admin-tables-field-pos-y' => [
                'en' => 'Position Y (%)',
                'lv' => 'Y pozīcija (%)',
                'ru' => 'Позиция Y (%)',
            ],
            'admin-tables-field-width' => [
                'en' => 'Width (px)',
                'lv' => 'Platums (px)',
                'ru' => 'Ширина (px)',
            ],
            'admin-tables-field-height' => [
                'en' => 'Height (px)',
                'lv' => 'Augstums (px)',
                'ru' => 'Высота (px)',
            ],
            'admin-tables-field-rotation' => [
                'en' => 'Rotation (deg)',
                'lv' => 'Rotācija (°)',
                'ru' => 'Поворот (°)',
            ],
            'admin-tables-field-active' => [
                'en' => 'Table is active and visible',
                'lv' => 'Galdiņš ir aktīvs un redzams',
                'ru' => 'Стол активен и виден',
            ],
            'admin-tables-create-submit' => [
                'en' => 'Create table',
                'lv' => 'Izveidot galdiņu',
                'ru' => 'Создать стол',
            ],
            'admin-tables-cancel-button' => [
                'en' => 'Cancel',
                'lv' => 'Atcelt',
                'ru' => 'Отменить',
            ],
            'admin-tables-edit-title' => [
                'en' => 'Edit table',
                'lv' => 'Rediģēt galdiņu',
                'ru' => 'Редактировать стол',
            ],
            'admin-tables-edit-subtitle' => [
                'en' => 'Change table parameters: :name',
                'lv' => 'Mainīt galdiņa parametrus: :name',
                'ru' => 'Изменить параметры стола: :name',
            ],
            'admin-tables-save-button' => [
                'en' => 'Save changes',
                'lv' => 'Saglabāt izmaiņas',
                'ru' => 'Сохранить изменения',
            ],

            'staff-header-reservations-link' => [
                'en' => 'Reservations',
                'lv' => 'Rezervācijas',
                'ru' => 'Бронирования',
            ],

            'staff-reservations-title' => [
                'en' => 'Reservations',
                'lv' => 'Rezervācijas',
                'ru' => 'Бронирования',
            ],
            'staff-reservations-subtitle' => [
                'en' => 'Manage table reservations, walk-in guests and phone bookings.',
                'lv' => 'Pārvaldi galdiņu rezervācijas, walk-in viesus un telefona zvanus.',
                'ru' => 'Управляйте бронями столов, гостями без брони и телефонными заказами.',
            ],
            'staff-reservations-date-apply' => [
                'en' => 'Show',
                'lv' => 'Parādīt',
                'ru' => 'Показать',
            ],
            'staff-reservations-plan-title' => [
                'en' => 'Hall plan for :date',
                'lv' => 'Zāles plāns :date',
                'ru' => 'План зала на :date',
            ],
            'staff-reservations-guests-short' => [
                'en' => 'guests',
                'lv' => 'viesi',
                'ru' => 'гостей',
            ],
            'staff-reservations-legend-occupied' => [
                'en' => 'Occupied',
                'lv' => 'Aizņemts',
                'ru' => 'Занят',
            ],
            'staff-reservations-legend-confirmed' => [
                'en' => 'Confirmed',
                'lv' => 'Apstiprināta',
                'ru' => 'Подтверждена',
            ],
            'staff-reservations-legend-pending' => [
                'en' => 'Pending (awaiting guest)',
                'lv' => 'Gaida viesi',
                'ru' => 'Ожидает гостя',
            ],
            'staff-reservations-legend-free' => [
                'en' => 'Free',
                'lv' => 'Brīvs',
                'ru' => 'Свободен',
            ],
            'staff-reservations-sidebar-title' => [
                'en' => 'New reservation',
                'lv' => 'Jauna rezervācija',
                'ru' => 'Новая бронь',
            ],
            'staff-reservations-selected-table-empty' => [
                'en' => 'Select a table on the plan.',
                'lv' => 'Izvēlies galdiņu plānā.',
                'ru' => 'Выберите стол на плане.',
            ],
            'staff-reservations-mode-phone' => [
                'en' => 'Phone',
                'lv' => 'Telefons',
                'ru' => 'Телефон',
            ],
            'staff-reservations-mode-walkin' => [
                'en' => 'Walk-in',
                'lv' => 'Walk-in',
                'ru' => 'Живой гость',
            ],
            'staff-reservations-field-date' => [
                'en' => 'Date',
                'lv' => 'Datums',
                'ru' => 'Дата',
            ],
            'staff-reservations-field-time' => [
                'en' => 'Time',
                'lv' => 'Laiks',
                'ru' => 'Время',
            ],
            'staff-reservations-field-duration' => [
                'en' => 'Duration (minutes)',
                'lv' => 'Ilgums (minūtēs)',
                'ru' => 'Длительность (минуты)',
            ],
            'staff-reservations-field-duration-help' => [
                'en' => 'Standard duration is 120 minutes.',
                'lv' => 'Standarta ilgums ir 120 minūtes.',
                'ru' => 'Стандартная длительность — 120 минут.',
            ],
            'staff-reservations-field-guests' => [
                'en' => 'Guests count',
                'lv' => 'Viesu skaits',
                'ru' => 'Количество гостей',
            ],
            'staff-reservations-field-guest-name' => [
                'en' => 'Guest name',
                'lv' => 'Viesa vārds',
                'ru' => 'Имя гостя',
            ],
            'staff-reservations-field-guest-phone' => [
                'en' => 'Guest phone',
                'lv' => 'Viesa telefons',
                'ru' => 'Телефон гостя',
            ],
            'staff-reservations-submit' => [
                'en' => 'Create reservation',
                'lv' => 'Izveidot rezervāciju',
                'ru' => 'Создать бронь',
            ],
            'staff-reservations-list-title' => [
                'en' => 'Reservations for this day',
                'lv' => 'Dienas rezervācijas',
                'ru' => 'Брони на этот день',
            ],
            'staff-reservations-table-label' => [
                'en' => 'Table',
                'lv' => 'Galdiņš',
                'ru' => 'Стол',
            ],
            'staff-reservations-empty' => [
                'en' => 'No reservations for this day yet.',
                'lv' => 'Šajā dienā vēl nav rezervāciju.',
                'ru' => 'На этот день ещё нет бронирований.',
            ],
            'staff-reservations-error-overlap' => [
                'en' => 'This time slot for the table is already taken.',
                'lv' => 'Šis laika posms šim galdiņam jau ir aizņemts.',
                'ru' => 'Этот временной слот для стола уже занят.',
            ],
            'staff-reservations-error-past' => [
                'en' => 'Cannot create reservation for past date.',
                'lv' => 'Nevar izveidot rezervāciju pagātnē.',
                'ru' => 'Невозможно создать бронь на прошедшую дату.',
            ],
            'staff-reservations-created' => [
                'en' => 'Reservation created.',
                'lv' => 'Rezervācija izveidota.',
                'ru' => 'Бронь создана.',
            ],
            'staff-reservations-updated' => [
                'en' => 'Reservation status updated.',
                'lv' => 'Rezervācijas statuss atjaunots.',
                'ru' => 'Статус брони обновлён.',
            ],
            'staff-reservations-guests-label' => [
                'en' => 'seats',
                'lv' => 'vietas',
                'ru' => 'мест',
            ],

            'client-reservations-badge' => [
                'en' => 'Your reservations',
                'lv' => 'Tavas rezervācijas',
                'ru' => 'Твои бронирования',
            ],
            'client-reservations-title' => [
                'en' => 'Book a table',
                'lv' => 'Rezervē galdiņu',
                'ru' => 'Забронировать стол',
            ],
            'client-reservations-subtitle' => [
                'en' => 'Choose a table on the plan and reserve your time.',
                'lv' => 'Izvēlies galdiņu plānā un rezervē savu laiku.',
                'ru' => 'Выбери стол на плане и забронируй время.',
            ],
            'client-reservations-plan-title' => [
                'en' => 'Hall plan for :date',
                'lv' => 'Zāles plāns :date',
                'ru' => 'План зала на :date',
            ],
            'client-reservations-date-apply' => [
                'en' => 'Show',
                'lv' => 'Parādīt',
                'ru' => 'Показать',
            ],
            'client-reservations-guests-short' => [
                'en' => 'guests',
                'lv' => 'viesi',
                'ru' => 'гостей',
            ],
            'client-reservations-status-busy' => [
                'en' => 'Busy',
                'lv' => 'Aizņemts',
                'ru' => 'Занят',
            ],
            'client-reservations-legend-occupied' => [
                'en' => 'Occupied',
                'lv' => 'Aizņemts',
                'ru' => 'Занят',
            ],
            'client-reservations-legend-confirmed' => [
                'en' => 'Confirmed',
                'lv' => 'Apstiprināta',
                'ru' => 'Подтверждена',
            ],
            'client-reservations-legend-pending' => [
                'en' => 'Pending',
                'lv' => 'Gaida viesi',
                'ru' => 'Ожидает гостя',
            ],
            'client-reservations-legend-free' => [
                'en' => 'Free',
                'lv' => 'Brīvs',
                'ru' => 'Свободен',
            ],
            'client-reservations-sidebar-title' => [
                'en' => 'Reservation details',
                'lv' => 'Rezervācijas dati',
                'ru' => 'Детали брони',
            ],
            'client-reservations-selected-table-empty' => [
                'en' => 'Tap a free table on the plan to select it.',
                'lv' => 'Uzklikšķini uz brīva galdiņa plānā, lai to izvēlētos.',
                'ru' => 'Нажми на свободный стол на плане, чтобы выбрать его.',
            ],
            'client-reservations-field-date' => [
                'en' => 'Date',
                'lv' => 'Datums',
                'ru' => 'Дата',
            ],
            'client-reservations-field-time' => [
                'en' => 'Time',
                'lv' => 'Laiks',
                'ru' => 'Время',
            ],
            'client-reservations-field-duration' => [
                'en' => 'Duration (minutes)',
                'lv' => 'Ilgums (minūtēs)',
                'ru' => 'Длительность (минуты)',
            ],
            'client-reservations-field-duration-help' => [
                'en' => 'Standard session is about 120 minutes.',
                'lv' => 'Standarta sesijas ilgums ap 120 minūtēm.',
                'ru' => 'Стандартная сессия — около 120 минут.',
            ],
            'client-reservations-field-guests' => [
                'en' => 'Guests count',
                'lv' => 'Viesu skaits',
                'ru' => 'Количество гостей',
            ],
            'client-reservations-submit' => [
                'en' => 'Confirm reservation',
                'lv' => 'Apstiprināt rezervāciju',
                'ru' => 'Подтвердить бронь',
            ],
            'client-reservations-my-title' => [
                'en' => 'Your recent reservations',
                'lv' => 'Tavas pēdējās rezervācijas',
                'ru' => 'Твои последние бронирования',
            ],
            'client-reservations-table-label' => [
                'en' => 'Table',
                'lv' => 'Galdiņš',
                'ru' => 'Стол',
            ],
            'client-reservations-guests-label' => [
                'en' => 'Guests',
                'lv' => 'Viesi',
                'ru' => 'Гостей',
            ],
            'client-reservations-my-empty' => [
                'en' => 'You have no reservations yet.',
                'lv' => 'Tev vēl nav rezervāciju.',
                'ru' => 'У тебя пока нет бронирований.',
            ],
            'client-reservations-rules' => [
                'en' => 'You can book 1 table per day. The reservation is held for 1 hour from the selected time and is available only with a verified email.',
                'lv' => 'Tu vari rezervēt 1 galdiņu dienā. Rezervācija tiek turēta 1 stundu no izvēlētā laika un ir pieejama tikai ar apstiprinātu e-pastu.',
                'ru' => 'Ты можешь забронировать 1 стол в день. Бронь держится 1 час с выбранного времени и доступна только при подтверждённой почте.',
            ],
            'client-reservations-error-email' => [
                'en' => 'To book a table, please verify your email address.',
                'lv' => 'Lai rezervētu galdiņu, lūdzu, apstiprini savu e-pasta adresi.',
                'ru' => 'Чтобы забронировать стол, подтверди свой e-mail.',
            ],
            'client-reservations-error-too-soon' => [
                'en' => 'You can book a table at least 1 hour in advance.',
                'lv' => 'Galdiņu var rezervēt vismaz 1 stundu iepriekš.',
                'ru' => 'Стол можно бронировать минимум за 1 час до начала.',
            ],
            'client-reservations-error-one-per-day' => [
                'en' => 'You can make only one reservation per day.',
                'lv' => 'Tu vari veikt tikai vienu rezervāciju dienā.',
                'ru' => 'Ты можешь сделать только одну бронь на день.',
            ],
            'client-reservations-error-overlap' => [
                'en' => 'This time slot for the table is already taken.',
                'lv' => 'Šis laika posms šim galdiņam jau ir aizņemts.',
                'ru' => 'Этот временной слот для стола уже занят.',
            ],
            'client-reservations-created' => [
                'en' => 'Reservation created. We are waiting for you.',
                'lv' => 'Rezervācija izveidota. Mēs tevi gaidām.',
                'ru' => 'Бронь создана. Мы тебя ждём.',
            ],

            'staff-reservations-legend-active' => [
                'en' => 'Active',
                'lv' => 'Aktīvs',
                'ru' => 'Активен',
            ],

            'staff-reservations-status-label' => [
                'en' => 'Status',
                'lv' => 'Statuss',
                'ru' => 'Статус',
            ],

            'staff-reservations-status-pending' => [
                'en' => 'Booked, guest not yet arrived',
                'lv' => 'Rezervēts, viesis vēl nav atnācis',
                'ru' => 'Забронировано, гость ещё не пришёл',
            ],
            'staff-reservations-status-active' => [
                'en' => 'Guest is at the table',
                'lv' => 'Viesis pie galdiņa',
                'ru' => 'Гость за столом',
            ],
            'staff-reservations-status-completed' => [
                'en' => 'Visit completed',
                'lv' => 'Apmeklējums pabeigts',
                'ru' => 'Визит завершён',
            ],
            'staff-reservations-status-cancelled' => [
                'en' => 'Cancelled',
                'lv' => 'Atcelts',
                'ru' => 'Отменено',
            ],
            'staff-reservations-status-expired' => [
                'en' => 'Expired (guest did not come)',
                'lv' => 'Beidzies (viesis neatnāca)',
                'ru' => 'Истекло (гость не пришёл)',
            ],

            'staff-reservations-arrive-button' => [
                'en' => 'Arrived',
                'lv' => 'Viesis atnācis',
                'ru' => 'Гость пришёл',
            ],
            'staff-reservations-complete-button' => [
                'en' => 'Completed',
                'lv' => 'Pabeigt vizīti',
                'ru' => 'Завершить визит',
            ],
            'staff-reservations-cancel-button' => [
                'en' => 'Cancel',
                'lv' => 'Atcelt',
                'ru' => 'Отменить',
            ],

            // флеши для смены статуса у персонала
            'staff-reservations-arrived' => [
                'en' => 'Reservation marked as active. Guest has arrived.',
                'lv' => 'Rezervācija atzīmēta kā aktīva. Viesis ir atnācis.',
                'ru' => 'Бронь переведена в статус «гость на месте».',
            ],
            'staff-reservations-completed' => [
                'en' => 'Visit completed, table is free again.',
                'lv' => 'Apmeklējums pabeigts, galdiņš atkal ir brīvs.',
                'ru' => 'Визит завершён, стол снова свободен.',
            ],
            'staff-reservations-cancelled' => [
                'en' => 'Reservation cancelled.',
                'lv' => 'Rezervācija atcelta.',
                'ru' => 'Бронь отменена.',
            ],

            // --- Client: reservations (new legends, statuses, cancel button) ---

            'client-reservations-legend-active' => [
                'en' => 'Guest at the table',
                'lv' => 'Viesis pie galdiņa',
                'ru' => 'Гость за столом',
            ],

            'client-reservations-status-active-short' => [
                'en' => 'In use',
                'lv' => 'Aizņemts',
                'ru' => 'Занят',
            ],
            'client-reservations-status-pending-short' => [
                'en' => 'Booked',
                'lv' => 'Rezervēts',
                'ru' => 'Забронирован',
            ],

            'client-reservations-status-label' => [
                'en' => 'Status',
                'lv' => 'Statuss',
                'ru' => 'Статус',
            ],

            'client-reservations-status-pending' => [
                'en' => 'Booked, waiting for your arrival',
                'lv' => 'Rezervēts, gaidām tavu ierašanos',
                'ru' => 'Забронировано, ждём твоего прихода',
            ],
            'client-reservations-status-active' => [
                'en' => 'You are at the table',
                'lv' => 'Tu jau esi pie galdiņa',
                'ru' => 'Ты уже за столом',
            ],
            'client-reservations-status-completed' => [
                'en' => 'Visit completed',
                'lv' => 'Apmeklējums pabeigts',
                'ru' => 'Визит завершён',
            ],
            'client-reservations-status-cancelled' => [
                'en' => 'Cancelled',
                'lv' => 'Atcelts',
                'ru' => 'Отменено',
            ],
            'client-reservations-status-expired' => [
                'en' => 'Expired (no show)',
                'lv' => 'Beidzies (neatnāci)',
                'ru' => 'Истекло (ты не пришёл)',
            ],

            'client-reservations-cancel-button' => [
                'en' => 'Cancel reservation',
                'lv' => 'Atcelt rezervāciju',
                'ru' => 'Отменить бронь',
            ],

            // флеши для клиента при отмене
            'client-reservations-cancelled' => [
                'en' => 'Your reservation has been cancelled.',
                'lv' => 'Tava rezervācija ir atcelta.',
                'ru' => 'Твоя бронь отменена.',
            ],
            'client-reservations-cancel-error' => [
                'en' => 'Unable to cancel this reservation. Please contact staff.',
                'lv' => 'Neizdevās atcelt šo rezervāciju. Lūdzu, sazinies ar personālu.',
                'ru' => 'Не получилось отменить эту бронь. Свяжись с персоналом.',
            ],

            'admin-tables-opening-hours-title' => [
                'en' => 'Opening hours by day',
                'lv' => 'Darba laiks pa nedēļas dienām',
                'ru' => 'Время работы по дням недели',
            ],

            'admin-weekday-mon' => [
                'en' => 'Monday',
                'lv' => 'Pirmdiena',
                'ru' => 'Понедельник',
            ],
            'admin-weekday-tue' => [
                'en' => 'Tuesday',
                'lv' => 'Otrdiena',
                'ru' => 'Вторник',
            ],
            'admin-weekday-wed' => [
                'en' => 'Wednesday',
                'lv' => 'Trešdiena',
                'ru' => 'Среда',
            ],
            'admin-weekday-thu' => [
                'en' => 'Thursday',
                'lv' => 'Ceturtdiena',
                'ru' => 'Четверг',
            ],
            'admin-weekday-fri' => [
                'en' => 'Friday',
                'lv' => 'Piektdiena',
                'ru' => 'Пятница',
            ],
            'admin-weekday-sat' => [
                'en' => 'Saturday',
                'lv' => 'Sestdiena',
                'ru' => 'Суббота',
            ],
            'admin-weekday-sun' => [
                'en' => 'Sunday',
                'lv' => 'Svētdiena',
                'ru' => 'Воскресенье',
            ],

            'admin-tables-opening-hours-open' => [
                'en' => 'Open',
                'lv' => 'Atvērts',
                'ru' => 'Открыто',
            ],

            'admin-tables-opening-hours-save-button' => [
                'en' => 'Save opening hours',
                'lv' => 'Saglabāt darba laiku',
                'ru' => 'Сохранить время работы',
            ],

            'admin-tables-table-actions' => [
                'en' => 'Actions',
                'lv' => 'Darbības',
                'ru' => 'Действия',
            ],
            'perigee-verif-email' => [
                'en' => 'Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.',
                'lv' => 'Paldies, ka reģistrējies! Pirms sākt darbu, lūdzu, apstiprini savu e-pasta adresi, noklikšķinot uz saites, ko mēs tev tikko nosūtījām. Ja neesi saņēmis e-pastu, mēs ar prieku nosūtīsim tev vēl vienu.',
                'ru' => 'Спасибо за регистрацию! Прежде чем начать, не мог бы ты подтвердить свой адрес электронной почты, перейдя по ссылке, которую мы только что отправили тебе по электронной почте? Если ты не получил письмо, мы с радостью отправим тебе другое.',
            ],

            'staff-reservations-error-closed' => [
                'en' => 'At this time the venue is closed. Please choose another time.',
                'lv' => 'Šajā laikā iestāde ir slēgta. Lūdzu, izvēlies citu laiku.',
                'ru' => 'В это время заведение закрыто. Пожалуйста, выберите другое время.',
            ],

            'staff-reservations-arrive-forbidden' => [
                'en' => 'You can only mark reservations with “pending” status as arrived.',
                'lv' => 'Kā atnākušu var atzīmēt tikai rezervāciju ar statusu “gaida viesi”.',
                'ru' => 'Отметить прибытие можно только для брони в статусе «ожидает гостя».',
            ],

            'staff-reservations-complete-forbidden' => [
                'en' => 'You can only complete reservations with “active” status.',
                'lv' => 'Pabeigt var tikai rezervācijas ar statusu “aktīvs”.',
                'ru' => 'Завершить можно только брони в статусе «активен».',
            ],

            'staff-reservations-cancel-forbidden' => [
                'en' => 'You can only cancel reservations that are pending or active.',
                'lv' => 'Atcelt var tikai rezervācijas, kas ir gaidošas vai aktīvas.',
                'ru' => 'Отменить можно только брони в статусе «ожидает гостя» или «активен».',
            ],

            'admin-tables-plan-save-button' => [
                'en' => 'Save layout',
                'lv' => 'Saglabāt plānu',
                'ru' => 'Сохранить план',
            ],
            'admin-tables-opening-hours-updated' => [
                'en' => 'Opening hours have been updated.',
                'lv' => 'Darba laiks ir atjaunināts.',
                'ru' => 'Время работы обновлено.',
            ],

            'admin-tables-layout-updated' => [
                'en' => 'Hall layout has been updated.',
                'lv' => 'Zāles plāns ir atjaunināts.',
                'ru' => 'План зала обновлён.',
            ],
            'client-reservations-error-past' => [
                'en' => 'You cannot make a reservation in the past. Please choose a future date and time.',
                'lv' => 'Nevar izveidot rezervāciju pagātnē. Lūdzu, izvēlies nākotnes datumu un laiku.',
                'ru' => 'Нельзя сделать бронь на прошедшее время. Пожалуйста, выберите дату и время в будущем.',
            ],

            'staff-reservations-arrive-too-early' => [
                'en' => 'It is too early to mark this reservation as arrived.',
                'lv' => 'Ir par agru, lai atzīmētu šo rezervāciju kā atnākušu.',
                'ru' => 'Слишком рано отмечать эту бронь как прибывшую.',
            ],

            'staff-reservations-cancel-client-forbidden' => [
                'en' => 'You cannot cancel reservations created by clients. They are managed automatically by the system.',
                'lv' => 'Nevari atcelt klientu izveidotas rezervācijas. Tās automātiski apstrādā sistēma.',
                'ru' => 'Вы не можете отменять брони, созданные клиентами. Ими автоматически управляет система.',
            ],

            'staff-header-orders-link' => [
                'en' => 'Orders',
                'lv' => 'Pasūtījumi',
                'ru' => 'Заказы',
            ],

            'staff-orders-title' => [
                'en' => 'Orders',
                'lv' => 'Pasūtījumi',
                'ru' => 'Заказы',
            ],

            'staff-orders-subtitle' => [
                'en' => 'Manage active and upcoming orders.',
                'lv' => 'Pārvaldiet aktīvos un gaidāmos pasūtījumus.',
                'ru' => 'Управление активными и предстоящими заказами.',
            ],

            'staff-orders-date-apply' => [
                'en' => 'Apply date',
                'lv' => 'Piemērot datumu',
                'ru' => 'Применить дату',
            ],

            'staff-orders-active-title' => [
                'en' => 'Orders for :date',
                'lv' => 'Pasūtījumi datumā :date',
                'ru' => 'Заказы на :date',
            ],

            'staff-orders-create-button' => [
                'en' => 'New Order',
                'lv' => 'Jauns pasūtījums',
                'ru' => 'Создать заказ',
            ],

            'staff-orders-empty' => [
                'en' => 'No orders found.',
                'lv' => 'Nav neviena pasūtījuma.',
                'ru' => 'Заказы отсутствуют.',
            ],

            'staff-orders-reservations-title' => [
                'en' => 'Upcoming reservations',
                'lv' => 'Gaidāmās rezervācijas',
                'ru' => 'Предстоящие резервации',
            ],

            'staff-orders-history-title' => [
                'en' => 'Completed/cancelled today (:date)',
                'lv' => 'Pabeigti/atcelti šodien (:date)',
                'ru' => 'Завершены/отменены сегодня (:date)',
            ],

            'staff-orders-history-empty' => [
                'en' => 'No completed or cancelled orders for this day.',
                'lv' => 'Šai dienai nav pabeigtu vai atceltu pasūtījumu.',
                'ru' => 'На эту дату нет завершённых или отменённых заказов.',
            ],

            'staff-orders-reservations-empty' => [
                'en' => 'No reservations for this date.',
                'lv' => 'Šajā datumā nav rezervāciju.',
                'ru' => 'На этот день нет резерваций.',
            ],

            'staff-orders-start-from-reservation-button' => [
                'en' => 'Start order',
                'lv' => 'Sākt pasūtījumu',
                'ru' => 'Начать заказ',
            ],

            'staff-orders-order-created' => [
                'en' => 'Order created from reservation.',
                'lv' => 'Pasūtījums izveidots no rezervācijas.',
                'ru' => 'Заказ создан на основе резервации.',
            ],

            'staff-orders-order-already-created' => [
                'en' => 'An order for this reservation already exists.',
                'lv' => 'Pasūtījums šai rezervācijai jau ir izveidots.',
                'ru' => 'Заказ для этой резервации уже создан.',
            ],

            'staff-orders-table-label' => [
                'en' => 'Table',
                'lv' => 'Galds',
                'ru' => 'Стол',
            ],

            'staff-orders-guest-label' => [
                'en' => 'Guest',
                'lv' => 'Viesis',
                'ru' => 'Гость',
            ],

            'staff-orders-status-label' => [
                'en' => 'Status',
                'lv' => 'Statuss',
                'ru' => 'Статус',
            ],

            'staff-orders-status-open' => [
                'en' => 'Open',
                'lv' => 'Atvērts',
                'ru' => 'Открыт',
            ],

            'staff-orders-status-completed' => [
                'en' => 'Completed',
                'lv' => 'Pabeigts',
                'ru' => 'Завершён',
            ],

            'staff-orders-status-cancelled' => [
                'en' => 'Cancelled',
                'lv' => 'Atcelts',
                'ru' => 'Отменён',
            ],

            'staff-orders-create-title' => [
                'en' => 'Create Order',
                'lv' => 'Izveidot pasūtījumu',
                'ru' => 'Создать заказ',
            ],

            'staff-orders-create-subtitle' => [
                'en' => 'Open a new order for a table.',
                'lv' => 'Atvērt jaunu pasūtījumu galdam.',
                'ru' => 'Открыть новый заказ для стола.',
            ],

            'staff-orders-create-error-validation' => [
                'en' => 'Please correct the highlighted fields.',
                'lv' => 'Lūdzu, izlabojiet atzīmētās kļūdas.',
                'ru' => 'Пожалуйста, исправьте выделенные ошибки.',
            ],

            'staff-orders-field-table' => [
                'en' => 'Table',
                'lv' => 'Galds',
                'ru' => 'Стол',
            ],

            'staff-orders-field-table-placeholder' => [
                'en' => 'Select table',
                'lv' => 'Izvēlieties galdu',
                'ru' => 'Выберите стол',
            ],

            'staff-orders-field-user-id-optional' => [
                'en' => 'Client ID (optional)',
                'lv' => 'Klienta ID (nav obligāts)',
                'ru' => 'ID клиента (необязательно)',
            ],

            'staff-orders-field-user-id-placeholder' => [
                'en' => 'Enter client ID',
                'lv' => 'Ievadiet klienta ID',
                'ru' => 'Введите ID клиента',
            ],

            'staff-orders-field-user-id-hint' => [
                'en' => 'Use only if you know the client account.',
                'lv' => 'Izmantojiet tikai tad, ja zināt klienta profilu.',
                'ru' => 'Используйте только если знаете аккаунт клиента.',
            ],

            'staff-orders-create-submit' => [
                'en' => 'Create',
                'lv' => 'Izveidot',
                'ru' => 'Создать',
            ],

            'staff-orders-show-badge' => [
                'en' => 'Order',
                'lv' => 'Pasūtījums',
                'ru' => 'Заказ',
            ],

            'staff-orders-show-title' => [
                'en' => 'Order №:id',
                'lv' => 'Pasūtījums №:id',
                'ru' => 'Заказ №:id',
            ],

            'staff-orders-show-subtitle' => [
                'en' => 'Table :table',
                'lv' => 'Galds :table',
                'ru' => 'Стол :table',
            ],

            'staff-orders-total-label' => [
                'en' => 'Total',
                'lv' => 'Kopā',
                'ru' => 'Итого',
            ],

            'staff-orders-complete-button' => [
                'en' => 'Complete order',
                'lv' => 'Pabeigt pasūtījumu',
                'ru' => 'Завершить заказ',
            ],

            'staff-orders-cancel-button' => [
                'en' => 'Cancel order',
                'lv' => 'Atcelt pasūtījumu',
                'ru' => 'Отменить заказ',
            ],

            'staff-orders-completed' => [
                'en' => 'Order completed successfully.',
                'lv' => 'Pasūtījums veiksmīgi pabeigts.',
                'ru' => 'Заказ успешно завершён.',
            ],

            'staff-orders-apply-loyalty-applied' => [
                'en' => 'Loyalty points applied successfully.',
                'lv' => 'Lojalitātes punkti veiksmīgi pielietoti.',
                'ru' => 'Бонусные баллы успешно применены.',
            ],

            'staff-orders-apply-loyalty-forbidden' => [
                'en' => 'You cannot apply loyalty points to this order.',
                'lv' => 'Nevar piemērot lojalitātes punktus šim pasūtījumam.',
                'ru' => 'Вы не можете применить бонусные баллы к этому заказу.',
            ],

            'staff-orders-apply-loyalty-no-client' => [
                'en' => 'Attach a client first to apply loyalty points.',
                'lv' => 'Lai piemērotu lojalitātes punktus, vispirms piesaistiet klientu.',
                'ru' => 'Чтобы применить бонусные баллы, сначала привяжите клиента.',
            ],

            'staff-orders-apply-loyalty-exceeds-limit' => [
                'en' => 'The loyalty amount exceeds the available balance or order total.',
                'lv' => 'Lojalitātes summa pārsniedz pieejamo bilanci vai pasūtījuma kopsummu.',
                'ru' => 'Сумма бонусов превышает доступный баланс или сумму заказа.',
            ],

            'staff-orders-apply-loyalty-invalid-amount' => [
                'en' => 'The loyalty amount must be greater than zero.',
                'lv' => 'Lojalitātes summai jābūt lielākai par nulli.',
                'ru' => 'Сумма бонусов должна быть больше нуля.',
            ],

            'staff-orders-cancelled' => [
                'en' => 'Order cancelled.',
                'lv' => 'Pasūtījums atcelts.',
                'ru' => 'Заказ отменён.',
            ],

            'staff-orders-complete-forbidden' => [
                'en' => 'You cannot complete this order.',
                'lv' => 'Nevar pabeigt šo pasūtījumu.',
                'ru' => 'Нельзя завершить этот заказ.',
            ],

            'staff-orders-cancel-forbidden' => [
                'en' => 'You cannot cancel this order.',
                'lv' => 'Nevar atcelt šo pasūtījumu.',
                'ru' => 'Нельзя отменить этот заказ.',
            ],

            'staff-orders-items-title' => [
                'en' => 'Order items',
                'lv' => 'Pasūtījuma pozīcijas',
                'ru' => 'Позиции заказа',
            ],

            'staff-orders-items-empty' => [
                'en' => 'No items in this order.',
                'lv' => 'Pasūtījumam nav nevienas pozīcijas.',
                'ru' => 'В заказе нет позиций.',
            ],

            'staff-orders-add-item-title' => [
                'en' => 'Add item',
                'lv' => 'Pievienot pozīciju',
                'ru' => 'Добавить позицию',
            ],

            'staff-orders-add-item-placeholder' => [
                'en' => 'Select item',
                'lv' => 'Izvēlieties pozīciju',
                'ru' => 'Выберите позицию',
            ],

            'staff-orders-add-item-submit' => [
                'en' => 'Add',
                'lv' => 'Pievienot',
                'ru' => 'Добавить',
            ],

            'staff-orders-item-added' => [
                'en' => 'Item added to order.',
                'lv' => 'Pozīcija pievienota pasūtījumam.',
                'ru' => 'Позиция добавлена в заказ.',
            ],

            'staff-orders-item-updated' => [
                'en' => 'Item updated.',
                'lv' => 'Pozīcija atjaunināta.',
                'ru' => 'Позиция обновлена.',
            ],

            'staff-orders-item-deleted' => [
                'en' => 'Item removed.',
                'lv' => 'Pozīcija dzēsta.',
                'ru' => 'Позиция удалена.',
            ],

            'staff-orders-add-item-forbidden' => [
                'en' => 'You cannot modify this order.',
                'lv' => 'Nevar rediģēt šo pasūtījumu.',
                'ru' => 'Нельзя изменять этот заказ.',
            ],

            'staff-orders-update-item-forbidden' => [
                'en' => 'You cannot update items in this order.',
                'lv' => 'Nevar atjaunināt šī pasūtījuma pozīcijas.',
                'ru' => 'Нельзя изменять позиции этого заказа.',
            ],

            'staff-orders-delete-item-forbidden' => [
                'en' => 'You cannot delete items from this order.',
                'lv' => 'Nevar dzēst pozīcijas no šī pasūtījuma.',
                'ru' => 'Нельзя удалять позиции из этого заказа.',
            ],

            'staff-orders-client-title' => [
                'en' => 'Client',
                'lv' => 'Klients',
                'ru' => 'Клиент',
            ],

            'staff-orders-client-empty' => [
                'en' => 'No client assigned.',
                'lv' => 'Klients nav pievienots.',
                'ru' => 'Клиент не привязан.',
            ],

            'staff-orders-attach-client-field' => [
                'en' => 'Client QR / loyalty code',
                'lv' => 'Klienta QR / lojalitātes kods',
                'ru' => 'QR / код лояльности клиента',
            ],

            'staff-orders-attach-client-placeholder' => [
                'en' => 'Enter loyalty code',
                'lv' => 'Ievadiet lojalitātes kodu',
                'ru' => 'Введите код лояльности',
            ],

            'staff-orders-attach-client-submit' => [
                'en' => 'Attach',
                'lv' => 'Pievienot',
                'ru' => 'Привязать',
            ],

            'staff-orders-client-loyalty-balance' => [
                'en' => 'Balance',
                'lv' => 'Lojalitātes bilance',
                'ru' => 'Баланс лояльности',
            ],

            'staff-orders-loyalty-withdraw-label' => [
                'en' => 'Debit from client account',
                'lv' => 'Debetēt no klienta konta',
                'ru' => 'Снять со счёта клиента',
            ],

            'staff-orders-loyalty-amount-placeholder' => [
                'en' => 'Amount in €',
                'lv' => 'Summa €',
                'ru' => 'Сумма в €',
            ],

            'staff-orders-loyalty-use-all' => [
                'en' => 'Use all balance',
                'lv' => 'Izmantot visu bilanci',
                'ru' => 'Вся сумма',
            ],

            'staff-orders-loyalty-apply' => [
                'en' => 'Apply',
                'lv' => 'Pielietot',
                'ru' => 'Применить',
            ],

            'staff-orders-loyalty-hint' => [
                'en' => 'Enter the amount to debit (not more than balance and not more than the total order amount).',
                'lv' => 'Ievadiet summu debetēšanai (ne vairāk kā bilance un ne vairāk kā kopējā pasūtījuma summa).',
                'ru' => 'Введите сумму списания (не больше баланса и не больше итоговой суммы заказа).',
            ],

            'staff-orders-attach-client-hint' => [
                'en' => 'Scan or type the QR code readable value.',
                'lv' => 'Skenējiet vai ievadiet QR koda vērtību.',
                'ru' => 'Отсканируйте или введите значение QR-кода.',
            ],

            'staff-orders-attach-client-not-found' => [
                'en' => 'Client not found.',
                'lv' => 'Klients nav atrasts.',
                'ru' => 'Клиент не найден.',
            ],

            'staff-orders-attach-client-success' => [
                'en' => 'Client attached to order successfully.',
                'lv' => 'Klients veiksmīgi pievienots pasūtījumam.',
                'ru' => 'Клиент успешно добавлен к заказу.',
            ],

            'staff-orders-attach-client-forbidden' => [
                'en' => 'The client of a finished order cannot be changed.',
                'lv' => 'Pabeigta pasūtījuma klientu nevar mainīt.',
                'ru' => 'Клиента завершённого заказа нельзя изменить.',
            ],

            'staff-orders-attach-client-loyalty-spent' => [
                'en' => 'The client cannot be changed after loyalty points were spent on this order.',
                'lv' => 'Klientu nevar mainīt, jo šim pasūtījumam jau izmantoti lojalitātes punkti.',
                'ru' => 'Клиента нельзя изменить: на этот заказ уже потрачены бонусные баллы.',
            ],

            'staff-orders-error-reservation-closed' => [
                'en' => 'This reservation is cancelled, expired or completed, so an order cannot be started from it.',
                'lv' => 'Šī rezervācija ir atcelta, beigusies vai pabeigta, tāpēc no tās nevar izveidot pasūtījumu.',
                'ru' => 'Эта бронь отменена, истекла или завершена, поэтому создать из неё заказ нельзя.',
            ],

            'staff-promo-title' => [
                'en' => 'Promo Codes',
                'lv' => 'Promokodi',
                'ru' => 'Промокоды',
            ],

            'staff-promo-subtitle' => [
                'en' => 'Manage discount codes and campaigns.',
                'lv' => 'Pārvaldiet atlaides kodus un kampaņas.',
                'ru' => 'Управление скидочными промокодами.',
            ],

            'staff-promo-add-button' => [
                'en' => 'Add promo code',
                'lv' => 'Pievienot promokodu',
                'ru' => 'Добавить промокод',
            ],

            'staff-promo-empty' => [
                'en' => 'No promo codes available.',
                'lv' => 'Nav neviena promokoda.',
                'ru' => 'Промокоды отсутствуют.',
            ],

            'staff-promo-badge-active' => [
                'en' => 'Active',
                'lv' => 'Aktīvs',
                'ru' => 'Активен',
            ],

            'staff-promo-badge-inactive' => [
                'en' => 'Inactive',
                'lv' => 'Neaktīvs',
                'ru' => 'Неактивен',
            ],

            'staff-promo-edit-button' => [
                'en' => 'Edit',
                'lv' => 'Rediģēt',
                'ru' => 'Редактировать',
            ],

            'staff-promo-delete-button' => [
                'en' => 'Delete',
                'lv' => 'Dzēst',
                'ru' => 'Удалить',
            ],

            'staff-promo-delete-confirm' => [
                'en' => 'Are you sure?',
                'lv' => 'Vai tiešām dzēst?',
                'ru' => 'Вы уверены?',
            ],
            'staff-promo-delete-modal-title' => [
                'en' => 'Confirm deletion',
                'lv' => 'Apstipriniet dzēšanu',
                'ru' => 'Подтвердите удаление',
            ],
            'staff-promo-delete-modal-text-prefix' => [
                'en' => 'Delete promo code',
                'lv' => 'Dzēst promokodu',
                'ru' => 'Удалить промокод',
            ],
            'staff-promo-delete-modal-text-note' => [
                'en' => 'This action cannot be undone.',
                'lv' => 'Šo darbību nevarēs atsaukt.',
                'ru' => 'Это действие нельзя будет отменить.',
            ],
            'staff-promo-delete-modal-cancel' => [
                'en' => 'Cancel',
                'lv' => 'Atcelt',
                'ru' => 'Отмена',
            ],
            'staff-promo-delete-modal-confirm' => [
                'en' => 'Delete',
                'lv' => 'Dzēst',
                'ru' => 'Удалить',
            ],

            'staff-promo-create-title' => [
                'en' => 'Create Promo Code',
                'lv' => 'Izveidot promokodu',
                'ru' => 'Создать промокод',
            ],

            'staff-promo-create-subtitle' => [
                'en' => 'Set up terms and discounts.',
                'lv' => 'Iestatiet promokoda parametrus.',
                'ru' => 'Настройте параметры промокода.',
            ],

            'staff-promo-edit-title' => [
                'en' => 'Edit Promo Code',
                'lv' => 'Rediģēt promokodu',
                'ru' => 'Редактировать промокод',
            ],

            'staff-promo-edit-subtitle' => [
                'en' => 'Update terms and discounts.',
                'lv' => 'Atjauniniet promokoda parametrus.',
                'ru' => 'Обновите параметры промокода.',
            ],

            'staff-promo-validation-error' => [
                'en' => 'Please correct the highlighted fields.',
                'lv' => 'Lūdzu, izlabojiet atzīmētās kļūdas.',
                'ru' => 'Исправьте выделенные ошибки.',
            ],

            'staff-promo-field-code' => [
                'en' => 'Code',
                'lv' => 'Kods',
                'ru' => 'Код',
            ],

            'staff-promo-field-description' => [
                'en' => 'Description',
                'lv' => 'Apraksts',
                'ru' => 'Описание',
            ],

            'staff-promo-field-discount-type' => [
                'en' => 'Discount type',
                'lv' => 'Atlaides veids',
                'ru' => 'Тип скидки',
            ],

            'staff-promo-field-discount-type-percent' => [
                'en' => 'Percentage',
                'lv' => 'Procenti',
                'ru' => 'Процентная',
            ],

            'staff-promo-field-discount-type-fixed' => [
                'en' => 'Fixed amount',
                'lv' => 'Fiksēta summa',
                'ru' => 'Фиксированная сумма',
            ],

            'staff-promo-field-discount-value' => [
                'en' => 'Discount value',
                'lv' => 'Atlaides vērtība',
                'ru' => 'Размер скидки',
            ],

            'staff-promo-field-max-uses' => [
                'en' => 'Max uses',
                'lv' => 'Maksimālais izmantošanas skaits',
                'ru' => 'Макс. использований',
            ],

            'staff-promo-field-max-uses-placeholder' => [
                'en' => 'Unlimited if empty',
                'lv' => 'Bez ierobežojuma, ja tukšs',
                'ru' => 'Без ограничений, если пусто',
            ],

            'staff-promo-field-valid-from' => [
                'en' => 'Valid from',
                'lv' => 'Derīgs no',
                'ru' => 'Действует с',
            ],

            'staff-promo-field-valid-until' => [
                'en' => 'Valid until',
                'lv' => 'Derīgs līdz',
                'ru' => 'Действует до',
            ],

            'staff-promo-field-active' => [
                'en' => 'Active',
                'lv' => 'Aktīvs',
                'ru' => 'Активен',
            ],

            'staff-promo-field-active-label' => [
                'en' => 'Enable code',
                'lv' => 'Ieslēgt kodu',
                'ru' => 'Включить код',
            ],

            'staff-promo-field-scope' => [
                'en' => 'Scope',
                'lv' => 'Darbības zona',
                'ru' => 'Область действия',
            ],

            'staff-promo-field-scope-all' => [
                'en' => 'All menu',
                'lv' => 'Visa ēdienkarte',
                'ru' => 'Всё меню',
            ],

            'staff-promo-field-scope-category' => [
                'en' => 'Category',
                'lv' => 'Kategorija',
                'ru' => 'Категория',
            ],

            'staff-promo-field-scope-item' => [
                'en' => 'Specific item',
                'lv' => 'Konkrēta pozīcija',
                'ru' => 'Конкретная позиция',
            ],

            'staff-promo-field-category' => [
                'en' => 'Category',
                'lv' => 'Kategorija',
                'ru' => 'Категория',
            ],

            'staff-promo-field-category-placeholder' => [
                'en' => 'Select category',
                'lv' => 'Izvēlēties kategoriju',
                'ru' => 'Выберите категорию',
            ],

            'staff-promo-field-item' => [
                'en' => 'Item',
                'lv' => 'Pozīcija',
                'ru' => 'Позиция',
            ],

            'staff-promo-field-item-placeholder' => [
                'en' => 'Select item',
                'lv' => 'Izvēlēties pozīciju',
                'ru' => 'Выберите позицию',
            ],

            'staff-promo-submit' => [
                'en' => 'Save',
                'lv' => 'Saglabāt',
                'ru' => 'Сохранить',
            ],

            'staff-promo-created' => [
                'en' => 'Promo code created.',
                'lv' => 'Promokods izveidots.',
                'ru' => 'Промокод создан.',
            ],

            'staff-promo-updated' => [
                'en' => 'Promo code updated.',
                'lv' => 'Promokods atjaunināts.',
                'ru' => 'Промокод обновлён.',
            ],

            'staff-promo-deleted' => [
                'en' => 'Promo code deleted.',
                'lv' => 'Promokods dzēsts.',
                'ru' => 'Промокод удалён.',
            ],

            'staff-orders-promo-title' => [
                'en' => 'Promo code',
                'lv' => 'Promokods',
                'ru' => 'Промокод',
            ],

            'staff-orders-promo-placeholder' => [
                'en' => 'Enter promo code',
                'lv' => 'Ievadiet promokodu',
                'ru' => 'Введите промокод',
            ],

            'staff-orders-promo-submit' => [
                'en' => 'Apply',
                'lv' => 'Piemērot',
                'ru' => 'Применить',
            ],

            'staff-orders-promo-applied' => [
                'en' => 'Promo code applied.',
                'lv' => 'Promokods piemērots.',
                'ru' => 'Промокод применён.',
            ],

            'staff-orders-apply-promo-forbidden' => [
                'en' => 'You can apply promo only to open orders.',
                'lv' => 'Promo var piemērot tikai atvērtajiem pasūtījumiem.',
                'ru' => 'Промокод можно применять только к открытому заказу.',
            ],

            'staff-orders-promo-invalid' => [
                'en' => 'Promo code is invalid or expired.',
                'lv' => 'Promokods ir nederīgs vai beidzies.',
                'ru' => 'Промокод недействителен или истёк.',
            ],

            'staff-orders-promo-requires-client' => [
                'en' => 'Attach a client first to apply a promo code.',
                'lv' => 'Lai piemērotu promokodu, vispirms piesaistiet klientu.',
                'ru' => 'Чтобы применить промокод, сначала привяжите клиента.',
            ],

            'staff-orders-promo-no-eligible-items' => [
                'en' => 'No items in this order qualify for this promo.',
                'lv' => 'Neviena pasūtījuma pozīcija neatbilst šim promokodam.',
                'ru' => 'Нет позиций заказа, подходящих под этот промокод.',
            ],

            'staff-orders-promo-not-owned' => [
                'en' => 'Promo code is not claimed by this client.',
                'lv' => 'Promokods nepieder šim klientam.',
                'ru' => 'Промокод не принадлежит этому клиенту.',
            ],

            'staff-orders-promo-already-used-by-client' => [
                'en' => 'This client has already used this promo code.',
                'lv' => 'Klients jau ir izmantojis šo promokodu.',
                'ru' => 'Клиент уже использовал этот промокод.',
            ],

            'staff-orders-promo-locked' => [
                'en' => 'Promo codes cannot be applied to closed orders.',
                'lv' => 'Promokodus nevar piemērot slēgtiem pasūtījumiem.',
                'ru' => 'Промокоды нельзя применять к закрытым заказам.',
            ],

            'staff-orders-promo-already-applied' => [
                'en' => 'Promo code is already applied.',
                'lv' => 'Promokods jau ir piemērots.',
                'ru' => 'Промокод уже применён.',
            ],

            'staff-orders-promo-code-label' => [
                'en' => 'Promo code',
                'lv' => 'Promokods',
                'ru' => 'Промокод',
            ],

            'staff-orders-summary-title' => [
                'en' => 'Summary',
                'lv' => 'Kopsavilkums',
                'ru' => 'Сводка',
            ],

            'staff-orders-summary-subtotal' => [
                'en' => 'Subtotal',
                'lv' => 'Starpsumma',
                'ru' => 'Промежуточный итог',
            ],

            'staff-orders-summary-discount' => [
                'en' => 'Discount',
                'lv' => 'Atlaide',
                'ru' => 'Скидка',
            ],

            'staff-orders-summary-loyalty-discount' => [
                'en' => 'Loyalty discount',
                'lv' => 'Lojalitātes atlaide',
                'ru' => 'Скидка от лояльности',
            ],

            'staff-orders-summary-total' => [
                'en' => 'Total',
                'lv' => 'Kopā',
                'ru' => 'Итого',
            ],

            'client-orders-badge' => [
                'en' => 'Your orders',
                'lv' => 'Jūsu pasūtījumi',
                'ru' => 'Ваши заказы',
            ],

            'client-orders-title' => [
                'en' => 'Order history',
                'lv' => 'Pasūtījumu vēsture',
                'ru' => 'История заказов',
            ],

            'client-orders-subtitle' => [
                'en' => 'Here you can view all your completed orders.',
                'lv' => 'Šeit variet apskatīt visus savus pasūtījumus.',
                'ru' => 'Здесь отображаются все ваши завершённые заказы.',
            ],

            'client-orders-empty' => [
                'en' => 'You have no orders yet.',
                'lv' => 'Jums vēl nav neviena pasūtījuma.',
                'ru' => 'У вас пока нет заказов.',
            ],

            'client-orders-table-label' => [
                'en' => 'Table',
                'lv' => 'Galds',
                'ru' => 'Стол',
            ],

            'client-orders-status-label' => [
                'en' => 'Status',
                'lv' => 'Statuss',
                'ru' => 'Статус',
            ],

            'client-orders-status-open' => [
                'en' => 'Open',
                'lv' => 'Atvērts',
                'ru' => 'Открыт',
            ],

            'client-orders-status-completed' => [
                'en' => 'Completed',
                'lv' => 'Pabeigts',
                'ru' => 'Завершён',
            ],

            'client-orders-status-cancelled' => [
                'en' => 'Cancelled',
                'lv' => 'Atcelts',
                'ru' => 'Отменён',
            ],

            'client-orders-total-label' => [
                'en' => 'Total',
                'lv' => 'Kopā',
                'ru' => 'Итого',
            ],

            'client-orders-back-to-list' => [
                'en' => 'Back to orders',
                'lv' => 'Atpakaļ uz pasūtījumiem',
                'ru' => 'К списку заказов',
            ],
            'client-orders-back-tooltip' => [
                'en' => 'Back to orders list',
                'lv' => 'Atpakaļ uz pasūtījumu sarakstu',
                'ru' => 'К списку заказов',
            ],
            'client-orders-view-tooltip' => [
                'en' => 'View order',
                'lv' => 'Skatīt pasūtījumu',
                'ru' => 'Посмотреть заказ',
            ],
            'client-orders-subtotal-label' => [
                'en' => 'Subtotal',
                'lv' => 'Starpsumma',
                'ru' => 'Промежуточный итог',
            ],
            'client-orders-discount-promo-label' => [
                'en' => 'Promo code discount',
                'lv' => 'Atlaide ar promo kodu',
                'ru' => 'Скидка по промокоду',
            ],
            'client-orders-discount-loyalty-label' => [
                'en' => 'Loyalty spent',
                'lv' => 'Izlietotie bonusi',
                'ru' => 'Списано бонусов',
            ],
            'client-orders-discount-total-label' => [
                'en' => 'Total discount',
                'lv' => 'Kopējā atlaide',
                'ru' => 'Всего скидок',
            ],

            'client-orders-with-discount' => [
                'en' => 'With discount',
                'lv' => 'Ar atlaidi',
                'ru' => 'Скидка',
            ],

            'client-sidebar-loyalty' => [
                'en' => 'Loyalty',
                'lv' => 'Lojalitāte',
                'ru' => 'Лояльность',
            ],

            'client-loyalty-badge' => [
                'en' => 'Loyalty program',
                'lv' => 'Lojalitātes programma',
                'ru' => 'Программа лояльности',
            ],

            'client-loyalty-title' => [
                'en' => 'Your loyalty card',
                'lv' => 'Jūsu lojalitātes karte',
                'ru' => 'Ваша карта лояльности',
            ],

            'client-loyalty-subtitle' => [
                'en' => 'Show this QR code to earn bonuses.',
                'lv' => 'Uzskenējiet šo QR kodu, lai saņemtu bonusus.',
                'ru' => 'Покажите этот QR-код, чтобы получать бонусы.',
            ],

            'client-loyalty-qr-label' => [
                'en' => 'Your QR code',
                'lv' => 'Jūsu QR kods',
                'ru' => 'Ваш QR-код',
            ],

            'client-loyalty-qr-hint' => [
                'en' => 'Your QR code identifies you during payment.',
                'lv' => 'Šis QR kods identificē jūs norēķinos.',
                'ru' => 'Этот QR-код идентифицирует вас при оплате.',
            ],

            'client-loyalty-card-title' => [
                'en' => 'Loyalty details',
                'lv' => 'Lojalitātes detaļas',
                'ru' => 'Данные лояльности',
            ],

            'client-loyalty-card-text' => [
                'en' => 'This is your personal loyalty account.',
                'lv' => 'Šis ir jūsu personīgais lojalitātes konts.',
                'ru' => 'Это ваш персональный аккаунт лояльности.',
            ],

            'client-loyalty-card-holder' => [
                'en' => 'Name',
                'lv' => 'Vārds',
                'ru' => 'Имя',
            ],

            'client-loyalty-card-code' => [
                'en' => 'Code',
                'lv' => 'Kods',
                'ru' => 'Код',
            ],

            'client-loyalty-card-balance' => [
                'en' => 'Balance',
                'lv' => 'Bilance',
                'ru' => 'Баланс',
            ],

            'client-loyalty-earn-rule' => [
                'en' => 'You earn 1% of each completed order as loyalty points.',
                'lv' => 'Jūs saņemat 1% no katra pabeigtā pasūtījuma kā lojalitātes punktus.',
                'ru' => 'Вы получаете 1% от суммы каждого заказа бонусами на карту.',
            ],

            'staff-promo-discount-type-percent' => [
                'en' => ':value% off',
                'lv' => ':value% atlaide',
                'ru' => ':value% скидка',
            ],

            'staff-promo-discount-type-fixed' => [
                'en' => ':value € off',
                'lv' => ':value € atlaide',
                'ru' => ':value € скидка',
            ],

            'staff-promo-scope-all' => [
                'en' => 'All menu',
                'lv' => 'Visa ēdienkarte',
                'ru' => 'Всё меню',
            ],

            'staff-promo-scope-category' => [
                'en' => 'Category only',
                'lv' => 'Tikai kategorija',
                'ru' => 'Только категория',
            ],

            'staff-promo-scope-item' => [
                'en' => 'Specific item',
                'lv' => 'Konkrēta pozīcija',
                'ru' => 'Конкретная позиция',
            ],

            'staff-promo-uses-label' => [
                'en' => 'Uses',
                'lv' => 'Izmantošanas skaits',
                'ru' => 'Использований',
            ],

            'staff-promo-period-label' => [
                'en' => 'Validity',
                'lv' => 'Derīguma termiņš',
                'ru' => 'Период действия',
            ],
            'staff-header-promo-codes-link' => [
                'en' => 'Promocodes',
                'lv' => 'Promokodi',
                'ru' => 'Промокоды',
            ],
            'client-loyalty-history-title' => [
                'en' => 'Accrual history',
                'lv' => 'Uzkaitīšanas vēsture',
                'ru' => 'История начислений',
            ],

            'client-loyalty-history-empty' => [
                'en' => 'No bonuses have been accrued yet.',
                'lv' => 'Vēl nav uzkrātu bonusu.',
                'ru' => 'Пока ещё не было начислений.',
            ],

            'staff-orders-attach-client-scan-button' => [
                'en' => 'Scan QR',
                'lv' => 'Skenēt QR',
                'ru' => 'Сканировать QR',
            ],

            'staff-orders-attach-client-scan-title' => [
                'en' => 'Scan client QR code',
                'lv' => 'Skenējiet klienta QR kodu',
                'ru' => 'Сканирование QR клиента',
            ],

            'staff-orders-attach-client-scan-hint' => [
                'en' => 'Point the camera at the QR code on the client device.',
                'lv' => 'Pavērsiet kameru uz klienta ierīces QR kodu.',
                'ru' => 'Наведите камеру на QR-код с телефона клиента.',
            ],

            'staff-orders-attach-client-scan-error' => [
                'en' => 'Camera cannot be used for scanning.',
                'lv' => 'Neizdevās izmantot kameru skenēšanai.',
                'ru' => 'Не удалось использовать камеру для сканирования.',
            ],
            'staff-orders-open-button' => [
                'en' => 'Open order',
                'lv' => 'Atvert pasutijumu',
                'ru' => 'Открыть заказ',
            ],
            'staff-orders-item-update-button' => [
                'en' => 'Update item',
                'lv' => 'Atjauvināt pozīciju',
                'ru' => 'Обновить позицию',
            ],
            'staff-orders-item-delete-button' => [
                'en' => 'Delete item',
                'lv' => 'Nodzēst pozīciju',
                'ru' => 'Удалить позицию',
            ],
            'staff-orders-item-served' => [
                'en' => 'Served',
                'lv' => 'Pasniegts',
                'ru' => 'Отнесено',
            ],
            'staff-orders-item-not-served' => [
                'en' => 'Marked as not served',
                'lv' => 'Atzīmēts kā nepasniegts',
                'ru' => 'Отмечено как не отнесено',
            ],
            'staff-orders-error-table-occupied' => [
                'en' => 'This table is already occupied.',
                'lv' => 'Šis galds jau ir aizņemts.',
                'ru' => 'Этот стол уже занят.',
            ],
            'staff-orders-error-table-reserved' => [
                'en' => 'This table is reserved and not available for new orders.',
                'lv' => 'Šis galds ir rezervēts un nav pieejams jauniem pasūtījumiem.',
                'ru' => 'Этот стол зарезервирован и недоступен для нового заказа.',
            ],
            'staff-orders-error-table-reservation' => [
                'en' => 'It is not possible to start an order before the start time of the reservation.',
                'lv' => 'Pasūtījumu nav iespējams sākt pirms rezervācijas sākuma laika.',
                'ru' => 'Нельзя начинать заказ раньше начала времени резервации.',
            ],
            'staff-orders-error-table-has-open-order' => [
                'en' => 'This table already has an open order.',
                'lv' => 'Šim galdam jau ir atvērts pasūtījums.',
                'ru' => 'У этого столика уже есть открытый заказ.',
            ],
            'verif-email-new-link' => [
                'en' => 'A new verification link has been sent to the email address you provided during registration.',
                'lv' => 'Jauna verifikācijas saite ir nosūtīta uz jūsu reģistrācijas laikā norādīto e-pasta adresi.',
                'ru' => 'Новая ссылка для подтверждения была отправлена на адрес электронной почты, указанный вами при регистрации.',
            ],
            'verif-resend-button' => [
                'en' => 'Resend Verification Email',
                'lv' => 'Nosūtīt verifikācijas e-pastu vēlreiz',
                'ru' => 'Отправить письмо с подтверждением повторно',
            ],
            'verif-email-spam' => [
                'en' => 'If you recently changed your email address, please check the new inbox as well as the spam folder.',
                'lv' => 'Ja nesen esat mainījis savu e-pasta adresi, lūdzu, pārbaudiet jauno iesūtni, kā arī surogātpasta mapi.',
                'ru' => 'Если вы недавно изменили адрес электронной почты, пожалуйста, проверьте новый почтовый ящик, а также папку со спамом.',
            ],

            'client-promo-codes-badge' => [
                'en' => 'Promo codes',
                'lv' => 'Promo kodi',
                'ru' => 'Промокоды',
            ],

            'client-promo-codes-title' => [
                'en' => 'Your Promo Codes',
                'lv' => 'Jūsu promo kodi',
                'ru' => 'Ваши промокоды',
            ],

            'client-promo-codes-subtitle' => [
                'en' => 'Manage your active and used promo codes',
                'lv' => 'Pārvaldiet savus aktīvos un izmantotos promo kodus',
                'ru' => 'Управляйте своими активными и использованными промокодами',
            ],

            'client-promo-codes-add-title' => [
                'en' => 'Add a promo code',
                'lv' => 'Pievienot promo kodu',
                'ru' => 'Добавить промокод',
            ],

            'client-promo-codes-add-text' => [
                'en' => 'Enter a valid promo code to activate it.',
                'lv' => 'Ievadiet derīgu promo kodu, lai to aktivizētu.',
                'ru' => 'Введите действующий промокод, чтобы активировать его.',
            ],

            'client-promo-codes-input-label' => [
                'en' => 'Promo code',
                'lv' => 'Promo kods',
                'ru' => 'Промокод',
            ],

            'client-promo-codes-input-placeholder' => [
                'en' => 'Enter your promo code',
                'lv' => 'Ievadiet savu promo kodu',
                'ru' => 'Введите ваш промокод',
            ],

            'client-promo-codes-apply-button' => [
                'en' => 'Apply',
                'lv' => 'Pielietot',
                'ru' => 'Применить',
            ],

            'client-promo-codes-hint' => [
                'en' => 'Promo codes can be used for discounts or special offers.',
                'lv' => 'Promo kodus var izmantot atlaidēm vai īpašiem piedāvājumiem.',
                'ru' => 'Промокоды можно использовать для скидок или специальных предложений.',
            ],

            // --- List section ---
            'client-promo-codes-list-title' => [
                'en' => 'Your promo codes',
                'lv' => 'Jūsu promo kodi',
                'ru' => 'Ваши промокоды',
            ],

            'client-promo-codes-list-text' => [
                'en' => 'View all your active and used promo codes.',
                'lv' => 'Apskatiet visus savus aktīvos un izmantotos promo kodus.',
                'ru' => 'Просмотрите все свои активные и использованные промокоды.',
            ],

            'client-promo-codes-empty' => [
                'en' => 'You have no promo codes yet.',
                'lv' => 'Jums vēl nav promo kodu.',
                'ru' => 'У вас пока нет промокодов.',
            ],

            'client-promo-codes-discount-label' => [
                'en' => 'Discount',
                'lv' => 'Atlaide',
                'ru' => 'Скидка',
            ],

            'client-promo-codes-expires-label' => [
                'en' => 'Expires',
                'lv' => 'Derīgs līdz',
                'ru' => 'Истекает',
            ],

            'client-promo-claimed' => [
                'en' => 'Promo code applied successfully!',
                'lv' => 'Promo kods veiksmīgi pielietots!',
                'ru' => 'Промокод успешно применён!',
            ],

            'client-promo-not-found' => [
                'en' => 'Promo code not found.',
                'lv' => 'Promo kods nav atrasts.',
                'ru' => 'Промокод не найден.',
            ],

            'client-promo-already-claimed' => [
                'en' => 'You have already claimed this promo code.',
                'lv' => 'Jūs jau esat pieprasījis šo promo kodu.',
                'ru' => 'Вы уже применили этот промокод.',
            ],

            'client-promo-not-claimable' => [
                'en' => 'This promo code cannot be applied.',
                'lv' => 'Šo promo kodu nevar pielietot.',
                'ru' => 'Этот промокод не может быть применён.',
            ],

            // статусные метки (как в шаблоне)
            'promo-used' => [
                'en' => 'Used',
                'lv' => 'Izmantots',
                'ru' => 'Использован',
            ],

            'promo-expired' => [
                'en' => 'Expired',
                'lv' => 'Beidzies',
                'ru' => 'Истёк',
            ],

            'promo-active' => [
                'en' => 'Active',
                'lv' => 'Aktīvs',
                'ru' => 'Активен',
            ],

            'client-sidebar-promo-codes' => [
                'en' => 'Promo Codes',
                'lv' => 'Promokodi',
                'ru' => 'Промокоды',
            ],

            'staff-orders-promo-scan-button' => [
                'en' => 'Scan promo QR',
                'lv' => 'Skenēt promo QR',
                'ru' => 'Скнировать QR промокода',
            ],

            'staff-orders-promo-field' => [
                'en' => 'Promo code QR / code',
                'lv' => 'QR / promo kods',
                'ru' => 'QR / код промокода',
            ],

            'staff-promo-field-public-code' => [
                'en' => 'Promo code',
                'lv' => 'Promokods',
                'ru' => 'Промокод',
            ],

            // Validation messages
            'validation.required' => [
                'en' => 'This field is required.',
                'lv' => 'Šis lauks ir obligāts.',
                'ru' => 'Это поле обязательно.',
            ],

            'validation.email' => [
                'en' => 'The email must be a valid email address.',
                'lv' => 'E-pastam jābūt derīgai e-pasta adresei.',
                'ru' => 'Это поле должно содержать корректный email.',
            ],

            'validation.indisposable' => [
                'en' => 'Temporary email addresses are not allowed.',
                'lv' => 'Pagaidu e-pasta adreses nav atļautas.',
                'ru' => 'Временные email адреса не допускаются.',
            ],

            'validation.birth_date_required' => [
                'en' => 'Please enter your date of birth.',
                'lv' => 'Lūdzu, ievadiet savu dzimšanas datumu.',
                'ru' => 'Пожалуйста, укажите дату рождения.',
            ],

            'validation.birth_date_age' => [
                'en' => 'You must be at least 20 years old to register.',
                'lv' => 'Jums jābūt vismaz 20 gadus vecam, lai reģistrētos.',
                'ru' => 'Для регистрации вам должно быть не менее 20 лет.',
            ],

            'validation.confirmed' => [
                'en' => 'The confirmation does not match.',
                'lv' => 'Apstiprinājums neatbilst.',
                'ru' => 'Подтверждение не совпадает.',
            ],

            'validation.min' => [
                'en' => 'This field must be at least :min characters.',
                'lv' => 'Šis lauks jābūt vismaz :min rakstzīmes.',
                'ru' => 'Это поле должно содержать минимум :min символов.',
            ],

            'validation.unique' => [
                'en' => 'This value is already taken.',
                'lv' => 'Šī vērtība jau ir izmantota.',
                'ru' => 'Это значение уже используется.',
            ],

            'validation.current_password' => [
                'en' => 'The current password is incorrect.',
                'lv' => 'Pašreizējā parole ir nepareiza.',
                'ru' => 'Текущий пароль неправильный.',
            ],

            'validation.min.string' => [
                'en' => 'This field must be at least :min characters.',
                'lv' => 'Šim laukam jābūt vismaz :min rakstzīmēm.',
                'ru' => 'Это поле должно содержать минимум :min символов.',
            ],

            'auth.password.new' => [
                'en' => 'New Password',
                'lv' => 'Jaunā parole',
                'ru' => 'Новый пароль',
            ],

            'auth.email-verification-failed' => [
                'en' => 'Registration successful, but the verification email could not be sent. Please try again later.',
                'lv' => 'Reģistrācija bija veiksmīga, bet verifikācijas e-pastu nevarēja nosūtīt. Lūdzu, mēģiniet vēlāk.',
                'ru' => 'Регистрация успешна, но письмо с подтверждением не удалось отправить. Пожалуйста, попробуйте позже.',
            ],

            'floor-plan-zone-terrace' => [
                'en' => 'Terrace',
                'lv' => 'Terase',
                'ru' => 'Терраса',
            ],

            'floor-plan-zone-hall1' => [
                'en' => 'Hall 1',
                'lv' => 'Zāle 1',
                'ru' => 'Зал 1',
            ],

            'floor-plan-zone-hall2' => [
                'en' => 'Hall 2',
                'lv' => 'Zāle 2',
                'ru' => 'Зал 2',
            ],

            'floor-plan-zone-playstation' => [
                'en' => 'PlayStation Zone',
                'lv' => 'PlayStation zona',
                'ru' => 'Зона с плейстейшн',
            ],

            'floor-plan-zone-vip' => [
                'en' => 'VIP',
                'lv' => 'VIP',
                'ru' => 'VIP',
            ],

            // Логи активности
            'logs-title' => [
                'en' => 'Activity Logs',
                'lv' => 'Aktivitātes žurnāli',
                'ru' => 'Логи активности',
            ],
            'logs-subtitle' => [
                'en' => 'History of all system actions',
                'lv' => 'Visu sistēmas darbību vēsture',
                'ru' => 'История всех действий в системе',
            ],
            'logs-filter-model-type' => [
                'en' => 'Object Type',
                'lv' => 'Objekta tips',
                'ru' => 'Тип объекта',
            ],
            'logs-filter-action' => [
                'en' => 'Action',
                'lv' => 'Darbība',
                'ru' => 'Действие',
            ],
            'logs-filter-model-id' => [
                'en' => 'Object ID',
                'lv' => 'Objekta ID',
                'ru' => 'ID объекта',
            ],
            'logs-filter-date-from' => [
                'en' => 'Date From',
                'lv' => 'Datums no',
                'ru' => 'Дата от',
            ],
            'logs-filter-date-to' => [
                'en' => 'Date To',
                'lv' => 'Datums līdz',
                'ru' => 'Дата до',
            ],
            'logs-filter-all' => [
                'en' => 'All',
                'lv' => 'Visi',
                'ru' => 'Все',
            ],
            'logs-filter-apply' => [
                'en' => 'Apply',
                'lv' => 'Pielietot',
                'ru' => 'Применить',
            ],
            'logs-filter-reset' => [
                'en' => 'Reset',
                'lv' => 'Atiestatīt',
                'ru' => 'Сбросить',
            ],
            'logs-action-created' => [
                'en' => 'Created',
                'lv' => 'Izveidots',
                'ru' => 'Создано',
            ],
            'logs-action-updated' => [
                'en' => 'Updated',
                'lv' => 'Atjaunināts',
                'ru' => 'Обновлено',
            ],
            'logs-action-deleted' => [
                'en' => 'Deleted',
                'lv' => 'Dzēsts',
                'ru' => 'Удалено',
            ],
            'logs-model-order' => [
                'en' => 'Order',
                'lv' => 'Pasūtījums',
                'ru' => 'Заказ',
            ],
            'logs-model-menuitem' => [
                'en' => 'Menu Item',
                'lv' => 'Ēdiens',
                'ru' => 'Блюдо',
            ],
            'logs-model-menucategory' => [
                'en' => 'Menu Category',
                'lv' => 'Ēdienkartes kategorija',
                'ru' => 'Категория меню',
            ],
            'logs-model-reservation' => [
                'en' => 'Reservation',
                'lv' => 'Rezervācija',
                'ru' => 'Бронирование',
            ],
            'logs-model-table' => [
                'en' => 'Table',
                'lv' => 'Galds',
                'ru' => 'Столик',
            ],
            'logs-model-user' => [
                'en' => 'User',
                'lv' => 'Lietotājs',
                'ru' => 'Пользователь',
            ],
            'logs-model-promocode' => [
                'en' => 'Promo Code',
                'lv' => 'Promocijas kods',
                'ru' => 'Промокод',
            ],
            'logs-table-time' => [
                'en' => 'Time',
                'lv' => 'Laiks',
                'ru' => 'Время',
            ],
            'logs-table-user' => [
                'en' => 'User',
                'lv' => 'Lietotājs',
                'ru' => 'Пользователь',
            ],
            'logs-table-action' => [
                'en' => 'Action',
                'lv' => 'Darbība',
                'ru' => 'Действие',
            ],
            'logs-table-object' => [
                'en' => 'Object',
                'lv' => 'Objekts',
                'ru' => 'Объект',
            ],
            'logs-table-ip-browser' => [
                'en' => 'IP / Browser',
                'lv' => 'IP / Pārlūks',
                'ru' => 'IP / Браузер',
            ],
            'logs-table-changes' => [
                'en' => 'Changes',
                'lv' => 'Izmaiņas',
                'ru' => 'Изменения',
            ],
            'logs-no-logs' => [
                'en' => 'No logs found',
                'lv' => 'Žurnāli nav atrasti',
                'ru' => 'Логи не найдены',
            ],
            'logs-system' => [
                'en' => 'System',
                'lv' => 'Sistēma',
                'ru' => 'Система',
            ],
            'logs-show-created-data' => [
                'en' => 'Show created data',
                'lv' => 'Rādīt izveidotos datus',
                'ru' => 'Показать созданные данные',
            ],
            'logs-show-deleted-data' => [
                'en' => 'Show deleted data',
                'lv' => 'Rādīt dzēstos datus',
                'ru' => 'Показать удаленные данные',
            ],
            'logs-no-changes' => [
                'en' => 'No changes',
                'lv' => 'Nav izmaiņu',
                'ru' => 'Нет изменений',
            ],
            'logs-field-count-one' => [
                'en' => 'field',
                'lv' => 'lauks',
                'ru' => 'поле',
            ],
            'logs-field-count-few' => [
                'en' => 'fields',
                'lv' => 'lauki',
                'ru' => 'поля',
            ],
            'logs-field-count-many' => [
                'en' => 'fields',
                'lv' => 'lauku',
                'ru' => 'полей',
            ],
            'logs-modal-title-created' => [
                'en' => 'Created Data',
                'lv' => 'Izveidotie dati',
                'ru' => 'Созданные данные',
            ],
            'logs-modal-title-updated' => [
                'en' => 'Changes',
                'lv' => 'Izmaiņas',
                'ru' => 'Изменения',
            ],
            'logs-modal-title-deleted' => [
                'en' => 'Deleted Data',
                'lv' => 'Dzēstie dati',
                'ru' => 'Удаленные данные',
            ],
            'logs-modal-was' => [
                'en' => 'Was:',
                'lv' => 'Bija:',
                'ru' => 'Было:',
            ],
            'logs-modal-became' => [
                'en' => 'Became:',
                'lv' => 'Kļuva:',
                'ru' => 'Стало:',
            ],
            'logs-modal-close' => [
                'en' => 'Close',
                'lv' => 'Aizvērt',
                'ru' => 'Закрыть',
            ],

            'common-back-to-order' => [
                'en' => 'Back to Order',
                'lv' => 'Atpakaļ uz pasūtījumu',
                'ru' => 'Вернуться к заказу',
            ],

            'staff-orders-select-items' => [
                'en' => 'Select items',
                'lv' => 'Izvēlēties pozīcijas',
                'ru' => 'Выбрать позиции',
            ],

            'staff-orders-summary-promo-discount' => [
                'en' => 'Promo code discount',
                'lv' => 'Atlaides kods',
                'ru' => 'Скидка по промокоду',
            ],
            'staff-orders-promo-scope-all-desc' => [
                'en' => 'Applied to entire order',
                'lv' => 'Piemērots visam pasūtījumam',
                'ru' => 'Применён на весь заказ',
            ],
            'staff-orders-promo-scope-category-desc' => [
                'en' => 'Applied to category',
                'lv' => 'Piemērots kategorijai',
                'ru' => 'Применён на категорию',
            ],
            'staff-orders-promo-scope-item-desc' => [
                'en' => 'Applied to item',
                'lv' => 'Piemērots pozīcijai',
                'ru' => 'Применён на позицию',
            ],
            'staff-orders-promo-eligible-amount' => [
                'en' => 'Eligible amount',
                'lv' => 'Piemērojamā summa',
                'ru' => 'Сумма для скидки',
            ],
            'staff-orders-promo-discount-percent' => [
                'en' => ':value% discount',
                'lv' => ':value% atlaide',
                'ru' => 'Скидка :value%',
            ],
            'staff-orders-promo-discount-fixed' => [
                'en' => 'Fixed :value € discount',
                'lv' => 'Fiksēta :value € atlaide',
                'ru' => 'Фиксированная скидка :value €',
            ],

            'staff-orders-with-discount' => [
                'en' => 'With discount',
                'lv' => 'Ar atlaidi',
                'ru' => 'Скидка',
            ],

            // Client - Orders - Promo Details
            'client-orders-promo-scope-all-desc' => [
                'en' => 'On entire order',
                'lv' => 'Uz visu pasūtījumu',
                'ru' => 'На весь заказ',
            ],
            'client-orders-promo-scope-category-desc' => [
                'en' => 'On category',
                'lv' => 'Uz kategoriju',
                'ru' => 'На категорию',
            ],
            'client-orders-promo-scope-item-desc' => [
                'en' => 'On item',
                'lv' => 'Uz pozīciju',
                'ru' => 'На позицию',
            ],
            'auth-failed' => [
                'en' => 'These credentials do not match our records.',
                'lv' => 'Šie akreditācijas dati nesakrīt ar mūsu ierakstiem.',
                'ru' => 'Данные для входа не совпадают с нашими записями.',
            ],
            'auth-password' => [
                'en' => 'The provided password is incorrect.',
                'lv' => 'Norādītā parole ir nepareiza.',
                'ru' => 'Указан неправильный пароль.',
            ],
            'auth-throttle' => [
                'en' => 'Too many login attempts. Please try again in :seconds seconds.',
                'lv' => 'Pārāk daudz mēģinājumu pierakstīties. Lūdzu, mēģiniet vēlreiz pēc :seconds sekundēm.',
                'ru' => 'Слишком много попыток входа. Пожалуйста, повторите попытку через :seconds секунд.',
            ],

            // Footer links and legal pages
            'footer-terms-conditions' => [
                'en' => 'Terms and Conditions',
                'lv' => 'Noteikumi un nosacījumi',
                'ru' => 'Условия использования',
            ],
            'footer-privacy-policy' => [
                'en' => 'Privacy Policy',
                'lv' => 'Privātuma politika',
                'ru' => 'Политика конфиденциальности',
            ],
            'footer-cookie-policy' => [
                'en' => 'Cookie Policy',
                'lv' => 'Sīkfailu politika',
                'ru' => 'Политика использования файлов cookie',
            ],
            'footer-contact-us' => [
                'en' => 'Contact Us',
                'lv' => 'Sazinies ar mums',
                'ru' => 'Свяжитесь с нами',
            ],

            // Registration form - Terms checkbox
            'register-terms-agree' => [
                'en' => 'I agree to the terms and conditions',
                'lv' => 'Es piekrītu lietošanas noteikumiem un nosacījumiem',
                'ru' => 'Я согласен(а) с условиями использования',
            ],
            'register-terms-link' => [
                'en' => 'Read the terms',
                'lv' => 'Izlasīt noteikumus',
                'ru' => 'Прочитать условия',
            ],

            // Registration form - Newsletter checkbox
            'register-newsletter-agree' => [
                'en' => 'I agree to receive special offers and promotions',
                'lv' => 'Es piekrītu saņemt īpašus piedāvājumus un akcijas',
                'ru' => 'Я согласен(а) получать специальные предложения и акции',
            ],

            // Page titles and update text
            'page-terms-title' => [
                'en' => 'Terms and Conditions',
                'lv' => 'Noteikumi un nosacījumi',
                'ru' => 'Условия использования',
            ],
            'page-privacy-title' => [
                'en' => 'Privacy Policy',
                'lv' => 'Privātuma politika',
                'ru' => 'Политика конфиденциальности',
            ],
            'page-cookies-title' => [
                'en' => 'Cookie Policy',
                'lv' => 'Sīkfailu politika',
                'ru' => 'Политика использования файлов cookie',
            ],
            'page-updated-label' => [
                'en' => 'Last updated',
                'lv' => 'Pēdējo reizi atjaunināts',
                'ru' => 'Последнее обновление',
            ],

            // Terms page sections (7 sections with title and text)
            'terms-section-1-title' => [
                'en' => 'General Terms',
                'lv' => 'Vispārējie noteikumi',
                'ru' => 'Общие условия',
            ],
            'terms-section-1-text' => [
                'en' => 'These Terms and Conditions were last updated on :date and are binding between you and PERIGEE LOUNGE. By accessing and using this website and its services, you agree to be bound by these terms.',
                'lv' => 'Šie noteikumi un nosacījumi tika pēdējo reizi atjaunināti :date un ir saistoši starp jums un PERIGEE LOUNGE. Piekļūstot šai vietnei un tās pakalpojumiem, jūs piekrītat būt saistītam ar šiem noteikumiem.',
                'ru' => 'Эти Условия использования были последний раз обновлены :date и являются обязательными между вами и PERIGEE LOUNGE. Получая доступ к этому веб-сайту и его услугам, вы соглашаетесь соблюдать эти условия.',
            ],
            'terms-section-2-title' => [
                'en' => 'Use License',
                'lv' => 'Izmantošanas licence',
                'ru' => 'Лицензия на использование',
            ],
            'terms-section-2-text' => [
                'en' => 'Permission is granted to temporarily download one copy of the materials (information or software) on PERIGEE LOUNGE\'s website for personal, non-commercial transitory viewing only. This is the grant of a license, not a transfer of title, and under this license you may not:',
                'lv' => 'Atļauja tiek dota uz laiku lejupielādēt PERIGEE LOUNGE vietnē esošo materiālu (informācijas vai programmatūras) vienu kopiju tikai personīgai, nekomercialai pagaidu skatīšanai. Šī ir licences piešķiršana, nevis virsraksta pārsūtīšana, un saskaņā ar šo licenci jūs nedrīkstat:',
                'ru' => 'Разрешение предоставляется временно скачивать одну копию материалов (информацию или программного обеспечения) с веб-сайта PERIGEE LOUNGE исключительно для личного, некоммерческого временного просмотра. Это предоставление лицензии, а не передача прав собственности, и в соответствии с этой лицензией вы не можете:',
            ],
            'terms-section-3-title' => [
                'en' => 'Restrictions',
                'lv' => 'Ierobežojumi',
                'ru' => 'Ограничения',
            ],
            'terms-section-3-text' => [
                'en' => 'You may not modify, reproduce, republish, upload, post, transmit, or distribute the content of this website in any form. The content on this website is protected by copyright law. You are prohibited from circumventing, disabling, or otherwise interfering with security-related features of this website.',
                'lv' => 'Jūs nedrīkstat modificēt, reproducēt, atkārtoti publicēt, augšupielādēt, publicēt, pārraidīt vai izplatīt šīs vietnes saturu jebkādā formā. Šīs vietnes saturs ir aizsargāts ar autortiesību likumu. Jums ir aizliegts apiet, deaktivizēt vai citādi iejaukt vietnes drošības funkcijās.',
                'ru' => 'Вы не должны изменять, воспроизводить, переиздавать, загружать, публиковать, передавать или распространять содержимое этого веб-сайта в любой форме. Содержимое этого веб-сайта защищено законом об авторском праве. Вам запрещено обходить, отключать или иным образом вмешиваться в функции безопасности этого веб-сайта.',
            ],
            'terms-section-4-title' => [
                'en' => 'User Content',
                'lv' => 'Lietotāja saturs',
                'ru' => 'Контент пользователя',
            ],
            'terms-section-4-text' => [
                'en' => 'If you post, upload, or publish any content to this website, you grant PERIGEE LOUNGE a worldwide, royalty-free license to use, copy, modify, and distribute that content. You represent and warrant that you own or have the necessary rights to such content and that it does not violate any third-party rights.',
                'lv' => 'Ja jūs publicējat, augšupielādējat vai publicējat kādu saturu šajā vietnē, jūs piešķirat PERIGEE LOUNGE pasaules mēroga, bezmaksas licenci lietot, kopēt, modificēt un izplatīt šo saturu. Jūs apstiprinājat un garantējat, ka jūs ņemat vai jums ir nepieciešamās tiesības uz šādu saturu un tas nepārkāpj nevienas trešās puses tiesības.',
                'ru' => 'Если вы публикуете, загружаете или публикуете какой-либо контент на этом веб-сайте, вы предоставляете PERIGEE LOUNGE мировую, беззалоговую лицензию на использование, копирование, изменение и распространение этого контента. Вы заявляете и гарантируете, что вы владеете или имеете необходимые права на такой контент и что он не нарушает права третьих лиц.',
            ],
            'terms-section-5-title' => [
                'en' => 'Disclaimer',
                'lv' => 'Atbildības ierobežojums',
                'ru' => 'Отказ от ответственности',
            ],
            'terms-section-5-text' => [
                'en' => 'The materials on PERIGEE LOUNGE\'s website are provided on an "as-is" basis. PERIGEE LOUNGE makes no warranties, expressed or implied, and hereby disclaims and negates all other warranties including, without limitation, implied warranties or conditions of merchantability, fitness for a particular purpose, or non-infringement.',
                'lv' => 'Materiāli PERIGEE LOUNGE vietnē tiek sniegtie uz "kā tā ir" bāzes. PERIGEE LOUNGE nesniedz garantijas, ne skaidri, ne netiešā veidā, un šeit atsakās un noliedz visas citas garantijas, ieskaitant, bez ierobežojumiem, netiešās garantijas vai komerciāluma piemērotības, piemērotības noteiktam nolūkam vai neaizsardzības neatbilstības.',
                'ru' => 'Материалы на веб-сайте PERIGEE LOUNGE предоставляются на основе «как есть». PERIGEE LOUNGE не предоставляет гарантий, ни явных, ни подразумеваемых, и настоящим отказывается и отрицает все другие гарантии, включая, без ограничения, подразумеваемые гарантии или условия товарообращаемости, пригодности для определённой цели или ненарушения.',
            ],
            'terms-section-6-title' => [
                'en' => 'Limitation of Liability',
                'lv' => 'Atbildības ierobežojums',
                'ru' => 'Ограничение ответственности',
            ],
            'terms-section-6-text' => [
                'en' => 'In no event shall PERIGEE LOUNGE or its suppliers be liable for any damages (including, without limitation, damages for loss of data or profit, or due to business interruption) arising out of the use or inability to use the materials on PERIGEE LOUNGE\'s website.',
                'lv' => 'Nekādam gadījumam PERIGEE LOUNGE vai tā piegādātāji nav atbildīgi par jebkādu kaitējumu (ieskaitant, bez ierobežojumiem, zaudējumus datu vai peļņas zudumam vai uzņēmējdarbības pārtraukuma dēļ), kas izriet no PERIGEE LOUNGE vietnes materiālu izmantošanas vai nespējas tos izmantot.',
                'ru' => 'Ни при каких обстоятельствах PERIGEE LOUNGE или его поставщики не несут ответственности за какие-либо убытки (включая, без ограничения, убытки из-за потери данных или прибыли или из-за перерыва в деятельности), вытекающие из использования или невозможности использования материалов на веб-сайте PERIGEE LOUNGE.',
            ],
            'terms-section-7-title' => [
                'en' => 'Governing Law',
                'lv' => 'Piemērojamais likums',
                'ru' => 'Применимое право',
            ],
            'terms-section-7-text' => [
                'en' => 'These terms and conditions are governed by and construed in accordance with the laws of Latvia, and you irrevocably submit to the exclusive jurisdiction of the courts located in Latvia.',
                'lv' => 'Šie noteikumi un nosacījumi tiek regulēti un interpretēti saskaņā ar Latvijas likumiem, un jūs neatsaucami iesniedzat eksklusīvajai Latvijas tiesas pūles jurisdikcijai.',
                'ru' => 'Эти условия регулируются законами Латвии и толкуются в соответствии с ними, и вы безотзывно подчиняетесь исключительной юрисдикции судов, находящихся в Латвии.',
            ],

            // Privacy Policy page sections (7 sections)
            'privacy-section-1-title' => [
                'en' => 'Introduction',
                'lv' => 'Ievads',
                'ru' => 'Введение',
            ],
            'privacy-section-1-text' => [
                'en' => 'At PERIGEE LOUNGE, we are committed to protecting your privacy. This Privacy Policy explains how we collect, use, and protect your personal information. Last updated: :date',
                'lv' => 'PERIGEE LOUNGE mēs esam apņemušies aizsargāt jūsu privātumu. Šī privātuma politika izskaidro, kā mēs vācam, izmantojam un aizsargājam jūsu personisko informāciju. Pēdējo reizi atjaunināts: :date',
                'ru' => 'В PERIGEE LOUNGE мы обязуемся защищать вашу конфиденциальность. Эта Политика конфиденциальности объясняет, как мы собираем, используем и защищаем вашу личную информацию. Последнее обновление: :date',
            ],
            'privacy-section-2-title' => [
                'en' => 'Information We Collect',
                'lv' => 'Informācija, ko mēs vācam',
                'ru' => 'Информация, которую мы собираем',
            ],
            'privacy-section-2-text' => [
                'en' => 'We collect the following types of information:',
                'lv' => 'Mēs vācam šādus informācijas veidus:',
                'ru' => 'Мы собираем следующие виды информации:',
            ],
            'privacy-section-2-item-1' => [
                'en' => 'Personal identification information (name, email address, phone number)',
                'lv' => 'Personiskās identifikācijas informācija (vārds, e-pasta adrese, tālruņa numurs)',
                'ru' => 'Информация личной идентификации (имя, адрес электронной почты, номер телефона)',
            ],
            'privacy-section-2-item-2' => [
                'en' => 'Reservation and ordering information',
                'lv' => 'Rezervācijas un pasūtīšanas informācija',
                'ru' => 'Информация о бронировании и заказах',
            ],
            'privacy-section-2-item-3' => [
                'en' => 'Payment information (processed securely)',
                'lv' => 'Maksājuma informācija (apstrādāta droši)',
                'ru' => 'Информация об оплате (обрабатывается безопасно)',
            ],
            'privacy-section-2-item-4' => [
                'en' => 'Device and usage data (IP address, browser type, pages visited)',
                'lv' => 'Ierīces un lietošanas dati (IP adrese, pārlūka tips, apmeklētās lapas)',
                'ru' => 'Данные об устройстве и использовании (IP-адрес, тип браузера, посещённые страницы)',
            ],
            'privacy-section-2-item-5' => [
                'en' => 'Communication data (inquiries, feedback, support requests)',
                'lv' => 'Komunikācijas dati (jautājumi, atsauksmes, atbalsta pieprasījumi)',
                'ru' => 'Данные о коммуникации (вопросы, отзывы, запросы поддержки)',
            ],
            'privacy-section-3-title' => [
                'en' => 'How We Use Your Information',
                'lv' => 'Kā mēs izmantojam jūsu informāciju',
                'ru' => 'Как мы используем вашу информацию',
            ],
            'privacy-section-3-text' => [
                'en' => 'We use the collected information for the following purposes:',
                'lv' => 'Mēs izmantojam savākto informāciju šādiem nolūkiem:',
                'ru' => 'Мы используем собранную информацию в следующих целях:',
            ],
            'privacy-section-3-item-1' => [
                'en' => 'Processing reservations and orders',
                'lv' => 'Rezervāciju un pasūtījumu apstrāde',
                'ru' => 'Обработка бронирований и заказов',
            ],
            'privacy-section-3-item-2' => [
                'en' => 'Sending confirmation emails and notifications',
                'lv' => 'Apstiprinājuma e-pastu un paziņojumu sūtīšana',
                'ru' => 'Отправка писем с подтверждением и уведомлений',
            ],
            'privacy-section-3-item-3' => [
                'en' => 'Customer support and issue resolution',
                'lv' => 'Klientu atbalsts un problēmu risināšana',
                'ru' => 'Поддержка клиентов и разрешение проблем',
            ],
            'privacy-section-3-item-4' => [
                'en' => 'Loyalty program management',
                'lv' => 'Lojalitātes programmas pārvaldība',
                'ru' => 'Управление программой лояльности',
            ],
            'privacy-section-3-item-5' => [
                'en' => 'Improving our services and website',
                'lv' => 'Mūsu pakalpojumu un vietnes uzlabošana',
                'ru' => 'Улучшение наших услуг и веб-сайта',
            ],
            'privacy-section-4-title' => [
                'en' => 'Data Security',
                'lv' => 'Datu drošība',
                'ru' => 'Безопасность данных',
            ],
            'privacy-section-4-text' => [
                'en' => 'We implement appropriate technical and organizational measures to protect your personal information from unauthorized access, alteration, disclosure, or destruction. Your data is stored securely on our servers.',
                'lv' => 'Mēs ieviešam atbilstošus tehniskos un organizatoriskos pasākumus, lai aizsargātu jūsu personisko informāciju pret neatļautu piekļuvi, pārveidošanu, izpaušanu vai iznīcināšanu. Jūsu dati tiek droši glabāti mūsu serveros.',
                'ru' => 'Мы применяем надлежащие технические и организационные меры для защиты вашей личной информации от несанкционированного доступа, изменения, раскрытия или уничтожения. Ваши данные безопасно хранятся на наших серверах.',
            ],
            'privacy-section-5-title' => [
                'en' => 'Data Retention',
                'lv' => 'Datu glabāšana',
                'ru' => 'Хранение данных',
            ],
            'privacy-section-5-text' => [
                'en' => 'We retain your personal information for as long as necessary to provide our services and fulfill the purposes outlined in this policy. You may request deletion of your account and data at any time.',
                'lv' => 'Mēs saglabājam jūsu personisko informāciju tik ilgi, cik nepieciešams, lai sniegtu mūsu pakalpojumus un izpildītu šajā politikā aprakstītos nolūkus. Jūs jebkurā laikā varat pieprasīt sava konta un datu dzēšanu.',
                'ru' => 'Мы храним вашу личную информацию столько времени, сколько необходимо для предоставления наших услуг и достижения целей, изложенных в этой политике. Вы можете запросить удаление своего аккаунта и данных в любое время.',
            ],
            'privacy-section-6-title' => [
                'en' => 'Your Rights',
                'lv' => 'Jūsu tiesības',
                'ru' => 'Ваши права',
            ],
            'privacy-section-6-text' => [
                'en' => 'You have the right to access, correct, and delete your personal information. You may also object to certain processing of your data. Contact us for any requests regarding your personal information.',
                'lv' => 'Jums ir tiesības piekļūt, labot un dzēst savu personisko informāciju. Jūs varat arī iebilst pret noteiktu jūsu datu apstrādi. Sazinieties ar mums par jebkādu pieprasījumu attiecībā uz jūsu personisko informāciju.',
                'ru' => 'У вас есть право доступа, исправления и удаления вашей личной информации. Вы также можете возражать против определённой обработки ваших данных. Свяжитесь с нами по поводу любых запросов, касающихся вашей личной информации.',
            ],
            'privacy-section-7-title' => [
                'en' => 'Contact Us',
                'lv' => 'Sazinies ar mums',
                'ru' => 'Свяжитесь с нами',
            ],
            'privacy-section-7-text' => [
                'en' => 'If you have any questions about this Privacy Policy, please contact us at hello@perigee-lounge.example or call +37100000000.',
                'lv' => 'Ja jums ir jautājumi par šo Privātuma politiku, lūdzu, sazinieties ar mums: hello@perigee-lounge.example vai zvaniet uz +37100000000.',
                'ru' => 'Если у вас есть вопросы по этой Политике конфиденциальности, пожалуйста, свяжитесь с нами по адресу hello@perigee-lounge.example или позвоните по телефону +37100000000.',
            ],

            // Cookie Policy page sections (7 sections)
            'cookies-section-1-title' => [
                'en' => 'What Are Cookies',
                'lv' => 'Kas ir sīkfaili',
                'ru' => 'Что такое файлы cookie',
            ],
            'cookies-section-1-text' => [
                'en' => 'Cookies are small text files that are stored on your device (computer, tablet, smartphone) when you visit websites. They help websites remember information about you and improve your browsing experience. Last updated: :date',
                'lv' => 'Sīkfaili ir mazi teksta faili, kas tiek glabāti jūsu ierīcē (dators, planšetdators, viedtālrunis), kad jūs apmeklējat vietnes. Tie palīdz vietnēm atcerēties informāciju par jums un uzlaboja jūsu pārlūkošanas pieredzi. Pēdējo reizi atjaunināts: :date',
                'ru' => 'Файлы cookie — это небольшие текстовые файлы, которые хранятся на вашем устройстве (компьютер, планшет, смартфон) при посещении веб-сайтов. Они помогают веб-сайтам запомнить информацию о вас и улучшить ваши впечатления от просмотра. Последнее обновление: :date',
            ],
            'cookies-section-2-title' => [
                'en' => 'Types of Cookies We Use',
                'lv' => 'Sīkfailu veidi, ko mēs izmantojam',
                'ru' => 'Типы файлов cookie, которые мы используем',
            ],
            'cookies-section-2-text' => [
                'en' => 'We use the following types of cookies on our website:',
                'lv' => 'Mēs izmantojam šādus sīkfailu veidus mūsu vietnē:',
                'ru' => 'Мы используем на нашем веб-сайте следующие типы файлов cookie:',
            ],
            'cookies-section-2-item-1' => [
                'en' => 'Essential Cookies: Required for website functionality (authentication, security)',
                'lv' => 'Būtski sīkfaili: Nepieciešami vietnes funkcionalitātei (autentifikācija, drošība)',
                'ru' => 'Необходимые файлы cookie: требуются для работы веб-сайта (аутентификация, безопасность)',
            ],
            'cookies-section-2-item-2' => [
                'en' => 'Analytics Cookies: Help us understand how you use our website',
                'lv' => 'Analītikas sīkfaili: Palīdz mums saprast, kā jūs izmantojat mūsu vietni',
                'ru' => 'Аналитические файлы cookie: помогают нам понять, как вы используете наш веб-сайт',
            ],
            'cookies-section-2-item-3' => [
                'en' => 'Preference Cookies: Remember your preferences and settings',
                'lv' => 'Preference sīkfaili: Atceras jūsu preferences un iestatījumus',
                'ru' => 'Файлы cookie предпочтений: запоминают ваши предпочтения и параметры',
            ],
            'cookies-section-3-title' => [
                'en' => 'How We Use Cookies',
                'lv' => 'Kā mēs izmantojam sīkfailus',
                'ru' => 'Как мы используем файлы cookie',
            ],
            'cookies-section-3-text' => [
                'en' => 'Our cookies are used to:',
                'lv' => 'Mūsu sīkfailus izmanto:',
                'ru' => 'Наши файлы cookie используются для:',
            ],
            'cookies-section-3-item-1' => [
                'en' => 'Keep you logged in and maintain your session',
                'lv' => 'Paturēt jūs pierakstījušos un uzturēt jūsu sesiju',
                'ru' => 'Держать вас в авторизованном состоянии и поддерживать вашу сессию',
            ],
            'cookies-section-3-item-2' => [
                'en' => 'Remember your preferences and language selection',
                'lv' => 'Atcerēties jūsu preferences un valodas atlasi',
                'ru' => 'Запомнить ваши предпочтения и выбор языка',
            ],
            'cookies-section-3-item-3' => [
                'en' => 'Track website usage and improve functionality',
                'lv' => 'Izsekot vietnes lietojumu un uzlabot funkcionalitāti',
                'ru' => 'Отслеживать использование веб-сайта и улучшать функциональность',
            ],
            'cookies-section-3-item-4' => [
                'en' => 'Provide personalized content and recommendations',
                'lv' => 'Sniegt personalizētu saturu un rekomendācijas',
                'ru' => 'Предоставить персонализированный контент и рекомендации',
            ],
            'cookies-section-3-item-5' => [
                'en' => 'Prevent fraud and enhance security',
                'lv' => 'Novērst krāpšanu un uzlabot drošību',
                'ru' => 'Предотвратить мошенничество и повысить безопасность',
            ],
            'cookies-section-4-title' => [
                'en' => 'Cookie Management',
                'lv' => 'Sīkfailu pārvaldība',
                'ru' => 'Управление файлами cookie',
            ],
            'cookies-section-4-text' => [
                'en' => 'You can control cookies through your browser settings. Most browsers allow you to refuse cookies or alert you when cookies are being sent. You can also delete cookies from your device.',
                'lv' => 'Jūs varat kontrolēt sīkfailus, izmantojot pārlūka iestatījumus. Lielākā daļa pārlūku ļauj jums noraidīt sīkfailus vai brīdināt jūs, kad tiek sūtīti sīkfaili. Jūs varat arī dzēst sīkfailus no savas ierīces.',
                'ru' => 'Вы можете управлять файлами cookie через параметры вашего браузера. Большинство браузеров позволяют вам отказать в использовании файлов cookie или предупредить вас при отправке файлов cookie. Вы также можете удалить файлы cookie с вашего устройства.',
            ],
            'cookies-section-5-title' => [
                'en' => 'Third-Party Cookies',
                'lv' => 'Trešo pušu sīkfaili',
                'ru' => 'Файлы cookie третьих сторон',
            ],
            'cookies-section-5-text' => [
                'en' => 'Some content on our website may be provided by third-party services, which may set their own cookies. We are not responsible for third-party cookie policies. Please review their privacy policies for more information.',
                'lv' => 'Dažus materiālus mūsu vietnē var sniegt trešo pušu pakalpojumi, kas var iestatīt savus sīkfailus. Mēs neatbildam par trešo pušu sīkfailu politiku. Lūdzu, pārskatiet viņu privātuma politiku, lai saņemtu plašāku informāciju.',
                'ru' => 'Часть контента на нашем веб-сайте может быть предоставлена сторонними сервисами, которые могут устанавливать свои собственные файлы cookie. Мы не несем ответственности за политику файлов cookie третьих лиц. Пожалуйста, ознакомьтесь с их политикой конфиденциальности для получения дополнительной информации.',
            ],
            'cookies-section-6-title' => [
                'en' => 'Legal Basis',
                'lv' => 'Juridiskā bāze',
                'ru' => 'Юридическое основание',
            ],
            'cookies-section-6-text' => [
                'en' => 'We use cookies based on your consent and our legitimate interests in operating our website efficiently. By continuing to use our website, you consent to our use of cookies as described in this policy.',
                'lv' => 'Mēs izmantojam sīkfailus, pamatojoties uz jūsu piekrišanu un mūsu leģitīmajām interesēm mūsu vietnes efektīvai darbībai. Turpinot izmantot mūsu vietni, jūs piekrītat mūsu sīkfailu lietošanai, kā aprakstīts šajā politikā.',
                'ru' => 'Мы используем файлы cookie на основе вашего согласия и наших законных интересов в эффективной работе нашего веб-сайта. Продолжая использовать наш веб-сайт, вы соглашаетесь с использованием файлов cookie, как описано в этой политике.',
            ],
            'cookies-section-7-title' => [
                'en' => 'Contact Us',
                'lv' => 'Sazinies ar mums',
                'ru' => 'Свяжитесь с нами',
            ],
            'cookies-section-7-text' => [
                'en' => 'If you have questions about our cookie use, please contact us at hello@perigee-lounge.example or +37100000000.',
                'lv' => 'Ja jums ir jautājumi par mūsu sīkfailu lietošanu, lūdzu, sazinieties ar mums: hello@perigee-lounge.example vai +37100000000.',
                'ru' => 'Если у вас есть вопросы по поводу нашего использования файлов cookie, пожалуйста, свяжитесь с нами по адресу hello@perigee-lounge.example или +37100000000.',
            ],

            // Pagination
            'pagination-showing' => [
                'en' => 'Showing',
                'lv' => 'Rāda',
                'ru' => 'Показано',
            ],
            'pagination-to' => [
                'en' => 'to',
                'lv' => 'līdz',
                'ru' => 'до',
            ],
            'pagination-of' => [
                'en' => 'of',
                'lv' => 'no',
                'ru' => 'из',
            ],
            'pagination-results' => [
                'en' => 'results',
                'lv' => 'rezultāti',
                'ru' => 'результатов',
            ],

            // Email verification
            'verify-email-title' => [
                'en' => 'Verify Email Address',
                'lv' => 'Apstiprini e-pasta adresi',
                'ru' => 'Подтверждение email адреса',
            ],

            // Profile / Laravel Breeze translations

            // Common form labels
            'common-password' => [
                'en' => 'Password',
                'lv' => 'Parole',
                'ru' => 'Пароль',
            ],

            // Common buttons
            'common-confirm' => [
                'en' => 'Confirm',
                'lv' => 'Apstiprināt',
                'ru' => 'Подтвердить',
            ],
            'common-guest' => [
                'en' => 'Guest',
                'lv' => 'Viesis',
                'ru' => 'Гость',
            ],
            'common-add' => [
                'en' => 'Add',
                'lv' => 'Pievienot',
                'ru' => 'Добавить',
            ],
            'common-categories' => [
                'en' => 'Categories',
                'lv' => 'Kategorijas',
                'ru' => 'Категории',
            ],
            'common-search-items' => [
                'en' => 'Search items',
                'lv' => 'Meklēt ēdienus',
                'ru' => 'Поиск по блюдам',
            ],
            'common-back-to-top' => [
                'en' => 'Back to top',
                'lv' => 'Uz augšu',
                'ru' => 'Вернуться в начало',
            ],
            'common-hours-placeholder' => [
                'en' => 'HH',
                'lv' => 'SS',
                'ru' => 'ЧЧ',
            ],
            'common-minutes-placeholder' => [
                'en' => 'MM',
                'lv' => 'MM',
                'ru' => 'ММ',
            ],
            'staff-menu-back-to-top' => [
                'en' => 'Back to top',
                'lv' => 'Uz augšu',
                'ru' => 'Вернуться в начало',
            ],
            'error-no-access' => [
                'en' => 'You do not have access to this page.',
                'lv' => 'Jums nav piekļuves šai lapai.',
                'ru' => 'У вас нет доступа к этой странице.',
            ],
            'table-not-found' => [
                'en' => 'Table not found',
                'lv' => 'Galds nav atrasts',
                'ru' => 'Стол не найден',
            ],
            'table-unavailable' => [
                'en' => 'This table is not available for ordering',
                'lv' => 'Šis galds nav pieejams pasūtīšanai',
                'ru' => 'Стол недоступен для заказа',
            ],
            'table-occupied' => [
                'en' => 'This table is already taken by another guest',
                'lv' => 'Šo galdu jau ir aizņēmis cits viesis',
                'ru' => 'Этот стол уже занят другим клиентом',
            ],
            'table-no-open-order' => [
                'en' => 'There is no open order for this table',
                'lv' => 'Šim galdam nav atvērta pasūtījuma',
                'ru' => 'Нет открытого заказа для этого стола',
            ],

            'table-no-open-order-hint' => [
                'en' => 'Your table isn\'t open yet. You can fill the cart now; ask a waiter to open the table before you send it.',
                'lv' => 'Jūsu galds vēl nav atvērts. Groziņu varat aizpildīt jau tagad; pirms nosūtīšanas palūdziet viesmīlim atvērt galdu.',
                'ru' => 'Ваш стол ещё не открыт. Корзину можно собрать уже сейчас; перед отправкой попросите официанта открыть стол.',
            ],

            'telegram-items-added-title' => [
                'en' => 'NEW ITEMS ADDED',
                'lv' => 'PIEVIENOTAS JAUNAS POZĪCIJAS',
                'ru' => 'НОВЫЕ ПОЗИЦИИ ДОБАВЛЕНЫ',
            ],

            'telegram-new-reservation-title' => [
                'en' => 'NEW RESERVATION',
                'lv' => 'JAUNA REZERVĀCIJA',
                'ru' => 'НОВАЯ БРОНЬ',
            ],

            'telegram-reservation-cancelled-title' => [
                'en' => 'RESERVATION CANCELLED',
                'lv' => 'REZERVĀCIJA ATCELTA',
                'ru' => 'БРОНЬ ОТМЕНЕНА',
            ],

            'telegram-table' => [
                'en' => 'Table',
                'lv' => 'Galds',
                'ru' => 'Стол',
            ],

            'telegram-client' => [
                'en' => 'Client',
                'lv' => 'Klients',
                'ru' => 'Клиент',
            ],

            'telegram-guest' => [
                'en' => 'Guest',
                'lv' => 'Viesis',
                'ru' => 'Гость',
            ],

            'telegram-order' => [
                'en' => 'Order',
                'lv' => 'Pasūtījums',
                'ru' => 'Заказ',
            ],

            'telegram-added' => [
                'en' => 'Added',
                'lv' => 'Pievienots',
                'ru' => 'Добавлены',
            ],

            'telegram-date-time' => [
                'en' => 'Date and time',
                'lv' => 'Datums un laiks',
                'ru' => 'Дата и время',
            ],

            'telegram-guests' => [
                'en' => 'Guests',
                'lv' => 'Viesi',
                'ru' => 'Гостей',
            ],

            'telegram-name' => [
                'en' => 'Name',
                'lv' => 'Vārds',
                'ru' => 'Имя',
            ],

            'telegram-status' => [
                'en' => 'Status',
                'lv' => 'Statuss',
                'ru' => 'Статус',
            ],

            'telegram-reservation-id' => [
                'en' => 'Reservation ID',
                'lv' => 'Rezervācijas ID',
                'ru' => 'ID брони',
            ],
            'order-sent-to-waiter' => [
                'en' => 'Your order was sent to the waiter',
                'lv' => 'Pasūtījums nosūtīts viesmīlim',
                'ru' => 'Заказ отправлен официанту',
            ],
            'staff-order-item-added' => [
                'en' => 'Item added to the order',
                'lv' => 'Pozīcija pievienota pasūtījumam',
                'ru' => 'Позиция добавлена в заказ',
            ],

            // Navigation

            // Confirm password page
            'confirm-password-title' => [
                'en' => 'Confirm Password',
                'lv' => 'Apstiprināt paroli',
                'ru' => 'Подтвердите пароль',
            ],
            'confirm-password-text' => [
                'en' => 'This is a secure area of the application. Please confirm your password before continuing.',
                'lv' => 'Šī ir droša lietojumprogrammas zona. Lūdzu, apstipriniet savu paroli pirms turpināšanas.',
                'ru' => 'Это защищённая область приложения. Пожалуйста, подтвердите пароль перед продолжением.',
            ],

            // Profile email verification

            // Menu - add to cart (for QR table order)

            // Admin translations
            'admin-translations-empty-value' => [
                'en' => 'Empty value',
                'lv' => 'Tukša vērtība',
                'ru' => 'Пустое значение',
            ],

            // Client reservations errors
            'client-reservations-cancel-forbidden' => [
                'en' => 'You cannot cancel this reservation.',
                'lv' => 'Jūs nevarat atcelt šo rezervāciju.',
                'ru' => 'Вы не можете отменить эту бронь.',
            ],
            'client-reservations-error-closed' => [
                'en' => 'The venue is closed at this time. Please choose another time.',
                'lv' => 'Šajā laikā iestāde ir slēgta. Lūdzu, izvēlieties citu laiku.',
                'ru' => 'В это время заведение закрыто. Пожалуйста, выберите другое время.',
            ],

            // Staff orders promo hint
            'staff-orders-promo-hint' => [
                'en' => 'Scan or enter a promo code.',
                'lv' => 'Skenējiet vai ievadiet promo kodu.',
                'ru' => 'Отсканируйте или введите промокод.',
            ],

            // Staff reservations errors
            'staff-reservations-error-table-has-open-order' => [
                'en' => 'This table already has an open order.',
                'lv' => 'Šim galdiņam jau ir atvērts pasūtījums.',
                'ru' => 'У этого столика уже есть открытый заказ.',
            ],
            'staff-reservations-arrive-too-late' => [
                'en' => 'It is too late to mark this reservation as arrived.',
                'lv' => 'Ir par vēlu atzīmēt šo rezervāciju kā ieradušos.',
                'ru' => 'Слишком поздно отмечать эту бронь как прибывшую.',
            ],

            // ========================
            // EMAIL TEMPLATES
            // ========================

            // Verify email
            'verify-email-email-title' => [
                'en' => 'Verify Your Email',
                'lv' => 'Apstipriniet savu e-pastu',
                'ru' => 'Подтвердите email',
            ],
            'verify-email-email-subtitle' => [
                'en' => 'Please click the button below to verify your email address.',
                'lv' => 'Lūdzu, noklikšķiniet uz pogas zemāk, lai apstiprinātu savu e-pasta adresi.',
                'ru' => 'Нажмите кнопку ниже, чтобы подтвердить ваш email адрес.',
            ],
            'verify-email-email-button' => [
                'en' => 'Verify Email',
                'lv' => 'Apstiprināt e-pastu',
                'ru' => 'Подтвердить email',
            ],
            'verify-email-email-expire' => [
                'en' => 'This link will expire in :count minutes.',
                'lv' => 'Šī saite būs derīga :count minūtes.',
                'ru' => 'Ссылка действительна :count минут.',
            ],
            'verify-email-email-ignore' => [
                'en' => 'If you did not create an account, no further action is required.',
                'lv' => 'Ja jūs neesat izveidojis kontu, turpmāka darbība nav nepieciešama.',
                'ru' => 'Если вы не создавали аккаунт, проигнорируйте это письмо.',
            ],
            'verify-email-email-regards' => [
                'en' => 'Best regards',
                'lv' => 'Ar cieņu',
                'ru' => 'С уважением',
            ],

            // Reset password email
            'reset-password-email-title' => [
                'en' => 'Reset Your Password',
                'lv' => 'Atjaunot paroli',
                'ru' => 'Сброс пароля',
            ],
            'reset-password-email-subtitle' => [
                'en' => 'You are receiving this email because we received a password reset request for your account.',
                'lv' => 'Jūs saņemat šo e-pastu, jo mēs saņēmām paroles atjaunošanas pieprasījumu jūsu kontam.',
                'ru' => 'Вы получили это письмо, потому что мы получили запрос на сброс пароля для вашего аккаунта.',
            ],
            'reset-password-email-button' => [
                'en' => 'Reset Password',
                'lv' => 'Atjaunot paroli',
                'ru' => 'Сбросить пароль',
            ],
            'reset-password-email-expire' => [
                'en' => 'This link will expire in :count minutes.',
                'lv' => 'Šī saite būs derīga :count minūtes.',
                'ru' => 'Ссылка действительна :count минут.',
            ],
            'reset-password-email-ignore' => [
                'en' => 'If you did not request a password reset, no further action is required.',
                'lv' => 'Ja jūs nepieprasījāt paroles atjaunošanu, turpmāka darbība nav nepieciešama.',
                'ru' => 'Если вы не запрашивали сброс пароля, проигнорируйте это письмо.',
            ],
            'reset-password-email-regards' => [
                'en' => 'Best regards',
                'lv' => 'Ar cieņu',
                'ru' => 'С уважением',
            ],

            // Reservation created email
            'reservation-email-received-title' => [
                'en' => 'Reservation Received',
                'lv' => 'Rezervācija saņemta',
                'ru' => 'Бронирование получено',
            ],
            'reservation-email-received-subtitle' => [
                'en' => 'We have received your reservation request',
                'lv' => 'Mēs esam saņēmuši jūsu rezervācijas pieprasījumu',
                'ru' => 'Мы получили ваш запрос на бронирование',
            ],
            'reservation-email-details' => [
                'en' => 'Reservation details',
                'lv' => 'Rezervācijas detaļas',
                'ru' => 'Детали бронирования',
            ],
            'reservation-email-date' => [
                'en' => 'Date',
                'lv' => 'Datums',
                'ru' => 'Дата',
            ],
            'reservation-email-time' => [
                'en' => 'Time',
                'lv' => 'Laiks',
                'ru' => 'Время',
            ],
            'reservation-email-guests' => [
                'en' => 'Guests',
                'lv' => 'Viesi',
                'ru' => 'Гости',
            ],
            'reservation-email-table' => [
                'en' => 'Table',
                'lv' => 'Galds',
                'ru' => 'Столик',
            ],
            'reservation-email-status' => [
                'en' => 'Status',
                'lv' => 'Statuss',
                'ru' => 'Статус',
            ],
            'reservation-email-cancel-note' => [
                'en' => 'You can cancel your reservation from your profile.',
                'lv' => 'Jūs varat atcelt rezervāciju savā profilā.',
                'ru' => 'Вы можете отменить бронирование в своём профиле.',
            ],
            'reservation-email-see-you' => [
                'en' => 'See you soon',
                'lv' => 'Tiksimies drīz',
                'ru' => 'До скорой встречи',
            ],

            // Reservation cancelled email
            'reservation-email-cancelled-title' => [
                'en' => 'Reservation Cancelled',
                'lv' => 'Rezervācija atcelta',
                'ru' => 'Бронирование отменено',
            ],
            'reservation-email-cancelled-subtitle' => [
                'en' => 'Your reservation has been cancelled',
                'lv' => 'Jūsu rezervācija ir atcelta',
                'ru' => 'Ваше бронирование было отменено',
            ],
            'reservation-email-cancelled-details' => [
                'en' => 'Cancelled reservation',
                'lv' => 'Atceltā rezervācija',
                'ru' => 'Отменённое бронирование',
            ],
            'reservation-email-hope-see' => [
                'en' => 'We hope to see you again soon!',
                'lv' => 'Ceram jūs drīz atkal redzēt!',
                'ru' => 'Надеемся скоро увидеть вас снова!',
            ],

            // Order completed email
            'order-email-title' => [
                'en' => 'Order Completed',
                'lv' => 'Pasūtījums pabeigts',
                'ru' => 'Заказ завершён',
            ],
            'order-email-subtitle' => [
                'en' => 'Thank you for your order!',
                'lv' => 'Paldies par jūsu pasūtījumu!',
                'ru' => 'Спасибо за ваш заказ!',
            ],
            'order-email-details' => [
                'en' => 'Order details',
                'lv' => 'Pasūtījuma detaļas',
                'ru' => 'Детали заказа',
            ],
            'order-email-number' => [
                'en' => 'Order',
                'lv' => 'Pasūtījums',
                'ru' => 'Заказ',
            ],
            'order-email-date' => [
                'en' => 'Date',
                'lv' => 'Datums',
                'ru' => 'Дата',
            ],
            'order-email-table' => [
                'en' => 'Table',
                'lv' => 'Galds',
                'ru' => 'Столик',
            ],
            'order-email-items' => [
                'en' => 'Ordered items',
                'lv' => 'Pasūtītās preces',
                'ru' => 'Заказанные позиции',
            ],
            'order-email-subtotal' => [
                'en' => 'Subtotal',
                'lv' => 'Starpsumma',
                'ru' => 'Подитог',
            ],
            'order-email-discount' => [
                'en' => 'Discount',
                'lv' => 'Atlaide',
                'ru' => 'Скидка',
            ],
            'order-email-total' => [
                'en' => 'Total',
                'lv' => 'Kopā',
                'ru' => 'Итого',
            ],
            'order-email-loyalty-earned' => [
                'en' => 'You earned :points loyalty points!',
                'lv' => 'Jūs nopelnījāt :points lojalitātes punktus!',
                'ru' => 'Вы заработали :points баллов лояльности!',
            ],
            'order-email-thanks' => [
                'en' => 'Thank you for visiting',
                'lv' => 'Paldies par apmeklējumu',
                'ru' => 'Спасибо за посещение',
            ],

            // Common email
            'email-regards' => [
                'en' => 'Best regards',
                'lv' => 'Ar cieņu',
                'ru' => 'С уважением',
            ],
        ];
    }
}
