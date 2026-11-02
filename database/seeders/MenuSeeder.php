<?php

namespace Database\Seeders;

use App\Models\MenuCategory;
use App\Models\MenuItem;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->menu() as $sortOrder => $block) {
            $category = MenuCategory::query()
                ->where('name->en', $block['name']['en'])
                ->first();

            $attributes = [
                'name' => $block['name'],
                'description' => $block['description'] ?? $this->blank(),
                'is_special' => $block['is_special'] ?? false,
                'sort_order' => $sortOrder,
            ];

            if ($category) {
                $category->update($attributes);
            } else {
                $category = MenuCategory::create($attributes);
            }

            foreach ($block['items'] as $index => $item) {
                $itemAttributes = [
                    'menu_category_id' => $category->id,
                    'name' => $item['name'],
                    'description' => $item['description'],
                    'allergens' => $item['allergens'] ?? null,
                    'price' => $item['price'],
                    'image_path' => null,
                    'sort_order' => $index,
                ];

                $existing = MenuItem::query()
                    ->where('menu_category_id', $category->id)
                    ->where('name->en', $item['name']['en'])
                    ->first();

                if ($existing) {
                    $existing->update($itemAttributes);
                } else {
                    MenuItem::create($itemAttributes);
                }
            }
        }
    }

    /**
     * @return array<string, string>
     */
    private function t(string $en, string $lv, string $ru): array
    {
        return ['en' => $en, 'lv' => $lv, 'ru' => $ru];
    }

    /**
     * @return array<string, string>
     */
    private function blank(): array
    {
        return $this->t('', '', '');
    }

    /**
     * Fictional demo menu for Perigee Lounge.
     *
     * @return array<int, array<string, mixed>>
     */
    private function menu(): array
    {
        return [
            [
                'name' => $this->t('Hookah', 'Ūdenspīpes', 'Кальяны'),
                'description' => $this->t('Blended to order', 'Gatavotas pēc pasūtījuma', 'Забиваем под заказ'),
                'items' => [
                    ['name' => $this->t('Aphelion', 'Aphelion', 'Aphelion'), 'description' => $this->t('Mint and bergamot. Light, clean and cool.', 'Piparmētra un bergamote. Viegla un vēsa.', 'Мята и бергамот. Лёгкий, чистый и прохладный.'), 'price' => 18.00],
                    ['name' => $this->t('Red Giant', 'Red Giant', 'Red Giant'), 'description' => $this->t('Pomegranate with a little chili warmth.', 'Granātābols ar nelielu čili asumu.', 'Гранат с лёгкой теплотой чили.'), 'price' => 20.00],
                    ['name' => $this->t('Cold Orbit', 'Cold Orbit', 'Cold Orbit'), 'description' => $this->t('Citrus blend served in an ice bowl.', 'Citrusaugļu maisījums ledus traukā.', 'Цитрусовый микс в ледяной чаше.'), 'price' => 22.00],
                    ['name' => $this->t('Event Horizon', 'Event Horizon', 'Event Horizon'), 'description' => $this->t('Dark berries and black tea. Dense and slow.', 'Tumšās ogas un melnā tēja. Bieza un lēna.', 'Тёмные ягоды и чёрный чай. Плотный и неспешный.'), 'price' => 21.00],
                    ['name' => $this->t('Solar Wind', 'Solar Wind', 'Solar Wind'), 'description' => $this->t('Peach, apricot and a touch of ginger.', 'Persiks, aprikoze un nedaudz ingvera.', 'Персик, абрикос и немного имбиря.'), 'price' => 19.00],
                    ['name' => $this->t('Lunar Tide', 'Lunar Tide', 'Lunar Tide'), 'description' => $this->t('Melon and white grape. Soft and sweet.', 'Melone un baltās vīnogas. Maiga un salda.', 'Дыня и белый виноград. Мягкий и сладкий.'), 'price' => 18.00],
                ],
            ],
            [
                'name' => $this->t('Tea & coffee', 'Tēja un kafija', 'Чай и кофе'),
                'items' => [
                    ['name' => $this->t('Black Tea Service', 'Melnās tējas kanna', 'Чайник чёрного чая'), 'description' => $this->t('A pot of smoky black tea with dried orange.', 'Dūmaina melnā tēja ar kaltētu apelsīnu.', 'Дымный чёрный чай с сушёным апельсином.'), 'price' => 6.00],
                    ['name' => $this->t('Jasmine Green Tea', 'Jasmīnu zaļā tēja', 'Зелёный чай с жасмином'), 'description' => $this->t('A light, floral pot for two.', 'Viegla, ziedu tēja diviem.', 'Лёгкий цветочный чайник на двоих.'), 'price' => 6.00],
                    ['name' => $this->t('Sea Buckthorn Tea', 'Smiltsērkšķu tēja', 'Облепиховый чай'), 'description' => $this->t('Sea buckthorn, honey and thyme.', 'Smiltsērkšķi, medus un timiāns.', 'Облепиха, мёд и тимьян.'), 'price' => 7.00],
                    ['name' => $this->t('Cardamom Coffee', 'Kafija ar kardamonu', 'Кофе с кардамоном'), 'description' => $this->t('Dark roast, lightly spiced.', 'Tumšs grauzdējums ar vieglu garšvielu noti.', 'Тёмная обжарка с лёгкими специями.'), 'price' => 4.50],
                    ['name' => $this->t('Espresso', 'Espresso', 'Эспрессо'), 'description' => $this->t('A single, short and strong.', 'Īss un stiprs.', 'Короткий и крепкий.'), 'price' => 3.00],
                ],
            ],
            [
                'name' => $this->t('Cold drinks', 'Aukstie dzērieni', 'Холодные напитки'),
                'items' => [
                    ['name' => $this->t('Sea Buckthorn Lemonade', 'Smiltsērkšķu limonāde', 'Облепиховый лимонад'), 'description' => $this->t('Tart, bright and house-made.', 'Skāba, spilgta, pašu gatavota.', 'Терпкий, яркий, домашний.'), 'price' => 6.00],
                    ['name' => $this->t('Cucumber Tonic', 'Gurķu toniks', 'Огуречный тоник'), 'description' => $this->t('Cucumber, lime and tonic water.', 'Gurķis, laims un toniks.', 'Огурец, лайм и тоник.'), 'price' => 5.50],
                    ['name' => $this->t('Iced Hibiscus', 'Ledus hibisks', 'Холодный каркаде'), 'description' => $this->t('Cold-brewed hibiscus with orange peel.', 'Auksti gatavots hibisks ar apelsīna miziņu.', 'Каркаде холодного заваривания с апельсиновой цедрой.'), 'price' => 5.00],
                    ['name' => $this->t('Sparkling Water', 'Gāzēts ūdens', 'Газированная вода'), 'description' => $this->t('A 0.5 l bottle.', '0,5 l pudele.', 'Бутылка 0,5 л.'), 'price' => 3.00],
                ],
            ],
            [
                'name' => $this->t('Cocktails', 'Kokteiļi', 'Коктейли'),
                'description' => $this->t('18+', '18+', '18+'),
                'items' => [
                    ['name' => $this->t('Perigee Sour', 'Perigee Sour', 'Perigee Sour'), 'description' => $this->t('Whisky, lemon and smoked honey.', 'Viskijs, citrons un kūpināts medus.', 'Виски, лимон и копчёный мёд.'), 'price' => 11.00],
                    ['name' => $this->t('Dark Matter', 'Dark Matter', 'Dark Matter'), 'description' => $this->t('Dark rum, coffee and orange bitters.', 'Tumšais rums, kafija un apelsīnu bitters.', 'Тёмный ром, кофе и апельсиновый биттер.'), 'price' => 12.00],
                    ['name' => $this->t('Nebula Spritz', 'Nebula Spritz', 'Nebula Spritz'), 'description' => $this->t('Sparkling wine, blackcurrant and soda.', 'Dzirkstošais vīns, upenes un soda.', 'Игристое вино, чёрная смородина и содовая.'), 'price' => 10.00],
                    ['name' => $this->t('Low Orbit Gin', 'Low Orbit Gin', 'Low Orbit Gin'), 'description' => $this->t('Gin, cucumber and elderflower.', 'Džins, gurķis un plūškoks.', 'Джин, огурец и бузина.'), 'price' => 11.00],
                ],
            ],
            [
                'name' => $this->t('Bites', 'Uzkodas', 'Закуски'),
                'items' => [
                    ['name' => $this->t('Smoked Almonds', 'Kūpinātas mandeles', 'Копчёный миндаль'), 'description' => $this->t('Warm, with sea salt.', 'Siltas, ar jūras sāli.', 'Тёплый, с морской солью.'), 'allergens' => $this->t('Nuts', 'Rieksti', 'Орехи'), 'price' => 5.00],
                    ['name' => $this->t('Mezze Board', 'Meze plate', 'Доска мезе'), 'description' => $this->t('Hummus, olives, flatbread and pickles.', 'Humuss, olīvas, plakanmaize un marinējumi.', 'Хумус, оливки, лепёшка и соленья.'), 'allergens' => $this->t('Gluten, sesame', 'Glutēns, sezams', 'Глютен, кунжут'), 'price' => 14.00],
                    ['name' => $this->t('Cheese Plate', 'Siera plate', 'Сырная тарелка'), 'description' => $this->t('Three cheeses with honey and walnuts.', 'Trīs sieri ar medu un valriekstiem.', 'Три сыра с мёдом и грецкими орехами.'), 'allergens' => $this->t('Milk, nuts', 'Piens, rieksti', 'Молоко, орехи'), 'price' => 13.00],
                    ['name' => $this->t('Marinated Olives', 'Marinētas olīvas', 'Маринованные оливки'), 'description' => $this->t('With lemon and rosemary.', 'Ar citronu un rozmarīnu.', 'С лимоном и розмарином.'), 'price' => 5.00],
                    ['name' => $this->t('Honey Baklava', 'Medus baklava', 'Медовая пахлава'), 'description' => $this->t('Walnut and honey, served warm.', 'Valrieksti un medus, pasniedz siltu.', 'Грецкий орех и мёд, подаётся тёплой.'), 'allergens' => $this->t('Gluten, nuts, milk', 'Glutēns, rieksti, piens', 'Глютен, орехи, молоко'), 'price' => 6.00],
                ],
            ],
            [
                'name' => $this->t('Late-night offers', 'Vēlā vakara piedāvājumi', 'Поздние предложения'),
                'description' => $this->t('After 22:00', 'Pēc 22:00', 'После 22:00'),
                'is_special' => true,
                'items' => [
                    ['name' => $this->t('Hookah & Tea', 'Ūdenspīpe un tēja', 'Кальян и чай'), 'description' => $this->t('Any classic blend with a pot of tea.', 'Jebkurš klasiskais maisījums ar tējas kannu.', 'Любой классический микс и чайник чая.'), 'price' => 22.00],
                    ['name' => $this->t('Two Cocktails', 'Divi kokteiļi', 'Два коктейля'), 'description' => $this->t('Any two cocktails from the menu.', 'Jebkuri divi kokteiļi no ēdienkartes.', 'Любые два коктейля из меню.'), 'price' => 19.00],
                    ['name' => $this->t('Table for Four', 'Galds četriem', 'Стол на четверых'), 'description' => $this->t('Two hookahs, a mezze board and a pot of tea.', 'Divas ūdenspīpes, meze plate un tējas kanna.', 'Два кальяна, доска мезе и чайник чая.'), 'price' => 49.00],
                ],
            ],
        ];
    }
}
