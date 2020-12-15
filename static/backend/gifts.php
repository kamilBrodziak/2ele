<div class="wrap">
    <style>
        input[type=text] {
            max-width: 1000px;
            width: 100%;
            min-width: 300px;
        }
    </style>
    <h1>Gifts</h1>
    <?php settings_errors(); ?>

    <form method="post" action="options.php">
        <?php
        settings_fields('2eleThemeGift');
        do_settings_sections('2eleThemeGift');
        submit_button();
        ?>
    </form>
</div>