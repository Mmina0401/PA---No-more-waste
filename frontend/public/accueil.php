<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include __DIR__ . "/entete.php";
?>

<style>
    :root {
        --nmw-bg: #F4F9F3;
        --nmw-green: #2E7D32;
        --nmw-blue: #2B7A9B;
        --nmw-leaf: #7BC96F;
        --nmw-sky: #B9E3F3;
        --nmw-yellow: #F2C94C;
        --nmw-white: #FFFFFF;
        --nmw-text: #263238;
        --nmw-muted: #66756C;
        --nmw-border: rgba(38, 50, 56, .10);
        --nmw-shadow: 0 18px 45px rgba(38, 82, 56, .10);
        --nmw-shadow-hover: 0 24px 60px rgba(38, 82, 56, .16);
    }

    body {
        background:
            radial-gradient(circle at 8% 12%, rgba(123, 201, 111, .14), transparent 28%),
            radial-gradient(circle at 92% 18%, rgba(185, 227, 243, .32), transparent 30%),
            var(--nmw-bg);
        color: var(--nmw-text);
        font-family: "Inter", Arial, sans-serif;
    }

    .nmw-home {
        overflow: hidden;
    }

    .nmw-hero {
        position: relative;
        isolation: isolate;
        padding: 72px 0 54px;
    }

    .nmw-hero::before,
    .nmw-hero::after {
        content: "";
        position: absolute;
        border-radius: 999px;
        filter: blur(1px);
        z-index: -1;
        pointer-events: none;
    }

    .nmw-hero::before {
        width: 360px;
        height: 360px;
        background: rgba(123, 201, 111, .16);
        top: -180px;
        right: -90px;
    }

    .nmw-hero::after {
        width: 260px;
        height: 260px;
        background: rgba(43, 122, 155, .10);
        bottom: -130px;
        left: -120px;
    }

    .nmw-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 13px;
        margin-bottom: 20px;
        border: 1px solid rgba(46, 125, 50, .16);
        border-radius: 999px;
        background: rgba(255, 255, 255, .78);
        color: var(--nmw-green);
        font-size: 13px;
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
        box-shadow: 0 8px 24px rgba(38, 82, 56, .06);
        backdrop-filter: blur(10px);
    }

    .nmw-title {
        max-width: 760px;
        margin: 0 auto 18px;
        font-family: "Roboto Condensed", "Arial Narrow", Arial, sans-serif;
        font-size: clamp(46px, 7vw, 82px);
        line-height: .95;
        font-weight: 800;
        letter-spacing: -.035em;
        color: var(--nmw-text);
    }

    .nmw-title .green { color: var(--nmw-green); }
    .nmw-title .blue { color: var(--nmw-blue); }

    .nmw-tagline {
        margin: 0 0 14px;
        color: var(--nmw-green);
        font-family: "Roboto Condensed", "Arial Narrow", Arial, sans-serif;
        font-weight: 700;
        font-size: 18px;
        letter-spacing: .13em;
        text-transform: uppercase;
    }

    .nmw-intro {
        max-width: 720px;
        margin: 0 auto;
        color: var(--nmw-muted);
        font-size: 17px;
        line-height: 1.75;
    }

    .nmw-impact-row {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 10px;
        margin-top: 28px;
    }

    .nmw-impact-pill {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 14px;
        border-radius: 999px;
        background: var(--nmw-white);
        border: 1px solid var(--nmw-border);
        color: var(--nmw-text);
        font-size: 14px;
        font-weight: 600;
        box-shadow: 0 8px 24px rgba(38, 82, 56, .05);
    }

    .nmw-impact-pill i { color: var(--nmw-green); }

    .nmw-section-head {
        max-width: 680px;
        margin: 0 auto 30px;
        text-align: center;
    }

    .nmw-section-kicker {
        margin-bottom: 7px;
        color: var(--nmw-blue);
        font-size: 13px;
        font-weight: 800;
        letter-spacing: .12em;
        text-transform: uppercase;
    }

    .nmw-section-title {
        margin: 0 0 10px;
        font-family: "Roboto Condensed", "Arial Narrow", Arial, sans-serif;
        font-size: clamp(30px, 4vw, 42px);
        font-weight: 800;
        color: var(--nmw-text);
    }

    .nmw-section-text {
        color: var(--nmw-muted);
        margin: 0;
        line-height: 1.7;
    }

    .nmw-actions {
        padding: 14px 0 82px;
    }

    .nmw-card {
        position: relative;
        height: 100%;
        padding: 28px;
        overflow: hidden;
        border: 1px solid var(--nmw-border);
        border-radius: 18px;
        background: rgba(255, 255, 255, .92);
        box-shadow: var(--nmw-shadow);
        transition: transform .25s ease, box-shadow .25s ease, border-color .25s ease;
    }

    .nmw-card::after {
        content: "";
        position: absolute;
        width: 130px;
        height: 130px;
        border-radius: 50%;
        top: -70px;
        right: -55px;
        opacity: .18;
        transition: transform .3s ease;
    }

    .nmw-card:hover {
        transform: translateY(-7px);
        box-shadow: var(--nmw-shadow-hover);
        border-color: rgba(46, 125, 50, .18);
    }

    .nmw-card:hover::after {
        transform: scale(1.15);
    }

    .nmw-card.services::after { background: var(--nmw-green); }
    .nmw-card.collecte::after { background: var(--nmw-blue); }
    .nmw-card.benevole::after { background: var(--nmw-leaf); }

    .nmw-icon-wrap {
        display: inline-flex;
        width: 58px;
        height: 58px;
        align-items: center;
        justify-content: center;
        margin-bottom: 22px;
        border-radius: 16px;
        font-size: 24px;
    }

    .services .nmw-icon-wrap {
        color: var(--nmw-green);
        background: rgba(123, 201, 111, .18);
    }

    .collecte .nmw-icon-wrap {
        color: var(--nmw-blue);
        background: rgba(185, 227, 243, .45);
    }

    .benevole .nmw-icon-wrap {
        color: #417C39;
        background: rgba(123, 201, 111, .23);
    }

    .nmw-card-label {
        display: block;
        margin-bottom: 8px;
        color: var(--nmw-muted);
        font-size: 12px;
        font-weight: 800;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .nmw-card h3 {
        margin: 0 0 12px;
        font-family: "Roboto Condensed", "Arial Narrow", Arial, sans-serif;
        font-size: 26px;
        font-weight: 800;
        color: var(--nmw-text);
    }

    .nmw-card p {
        min-height: 78px;
        margin-bottom: 24px;
        color: var(--nmw-muted);
        line-height: 1.65;
    }

    .nmw-btn {
        display: inline-flex;
        width: 100%;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding: 12px 15px;
        border: 0;
        border-radius: 14px;
        font-weight: 700;
        text-decoration: none;
        transition: transform .2s ease, filter .2s ease, box-shadow .2s ease;
    }

    .nmw-btn:hover {
        transform: translateY(-1px);
        filter: brightness(.98);
        text-decoration: none;
    }

    .nmw-btn-primary {
        color: #fff;
        background: var(--nmw-green);
        box-shadow: 0 10px 22px rgba(46, 125, 50, .18);
    }

    .nmw-btn-secondary {
        color: #fff;
        background: var(--nmw-blue);
        box-shadow: 0 10px 22px rgba(43, 122, 155, .18);
    }

    .nmw-btn-leaf {
        color: var(--nmw-text);
        background: var(--nmw-leaf);
        box-shadow: 0 10px 22px rgba(123, 201, 111, .22);
    }

    .nmw-values {
        margin-bottom: 70px;
    }

    .nmw-values-panel {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 0;
        overflow: hidden;
        border: 1px solid var(--nmw-border);
        border-radius: 18px;
        background: rgba(255, 255, 255, .72);
        box-shadow: 0 15px 40px rgba(38, 82, 56, .06);
    }

    .nmw-value {
        padding: 24px;
        text-align: center;
    }

    .nmw-value + .nmw-value {
        border-left: 1px solid var(--nmw-border);
    }

    .nmw-value i {
        display: block;
        margin-bottom: 10px;
        color: var(--nmw-blue);
        font-size: 21px;
    }

    .nmw-value strong {
        display: block;
        margin-bottom: 5px;
        font-family: "Roboto Condensed", "Arial Narrow", Arial, sans-serif;
        color: var(--nmw-text);
        font-size: 20px;
    }

    .nmw-value span {
        color: var(--nmw-muted);
        font-size: 14px;
    }

    @media (max-width: 767.98px) {
        .nmw-hero { padding-top: 48px; }
        .nmw-card p { min-height: auto; }
        .nmw-values-panel { grid-template-columns: 1fr; }

        .nmw-value + .nmw-value {
            border-left: 0;
            border-top: 1px solid var(--nmw-border);
        }
    }
</style>

<main class="nmw-home">

    <section class="nmw-hero">
        <div class="container text-center">

            <div class="nmw-eyebrow">
                <i class="fa-solid fa-leaf"></i>
                <?= t("home_eyebrow") ?>
            </div>

            <h1 class="nmw-title">
                <?= t("home_title_1") ?><br>
                <span class="green"><?= t("home_title_2") ?></span>
                <span class="blue"><?= t("home_title_3") ?></span>
            </h1>

            <p class="nmw-tagline">
                <?= t("home_tagline") ?>
            </p>

            <p class="nmw-intro">
                <?= t("home_intro") ?>
            </p>

            <div class="nmw-impact-row">

                <span class="nmw-impact-pill">
                    <i class="fa-solid fa-recycle"></i>
                    <?= t("home_anti_waste") ?>
                </span>

                <span class="nmw-impact-pill">
                    <i class="fa-solid fa-people-group"></i>
                    <?= t("home_local_solidarity") ?>
                </span>

                <span class="nmw-impact-pill">
                    <i class="fa-solid fa-earth-europe"></i>
                    <?= t("home_responsible_impact") ?>
                </span>

            </div>

        </div>
    </section>

    <section class="nmw-actions">

        <div class="container">

            <div class="nmw-section-head">

                <div class="nmw-section-kicker">
                    <?= t("home_act_with_us") ?>
                </div>

                <h2 class="nmw-section-title">
                    <?= t("home_action_title") ?>
                </h2>

                <p class="nmw-section-text">
                    <?= t("home_action_text") ?>
                </p>

            </div>

            <div class="row g-4">

                <div class="col-lg-4 col-md-6">

                    <article class="nmw-card services">

                        <div class="nmw-icon-wrap">
                            <i class="fa-solid fa-calendar-days"></i>
                        </div>

                        <span class="nmw-card-label">
                            <?= t("home_services_label") ?>
                        </span>

                        <h3><?= t("home_services_title") ?></h3>

                        <p>
                            <?= t("home_services_text") ?>
                        </p>

                        <a href="services.php" class="nmw-btn nmw-btn-primary">

                            <span><?= t("home_services_button") ?></span>

                            <i class="fa-solid fa-arrow-right"></i>

                        </a>

                    </article>

                </div>

                <div class="col-lg-4 col-md-6">

                    <article class="nmw-card collecte">

                        <div class="nmw-icon-wrap">
                            <i class="fa-solid fa-truck"></i>
                        </div>

                        <span class="nmw-card-label">
                            <?= t("home_collection_label") ?>
                        </span>

                        <h3><?= t("home_collection_title") ?></h3>

                        <p>
                            <?= t("home_collection_text") ?>
                        </p>

                        <a href="demande-collecte.php" class="nmw-btn nmw-btn-secondary">

                            <span><?= t("home_collection_button") ?></span>

                            <i class="fa-solid fa-arrow-right"></i>

                        </a>

                    </article>

                </div>

                <div class="col-lg-4 col-md-6 mx-md-auto mx-lg-0">

                    <article class="nmw-card benevole">

                        <div class="nmw-icon-wrap">
                            <i class="fa-solid fa-hand-holding-heart"></i>
                        </div>

                        <span class="nmw-card-label">
                            <?= t("home_volunteer_label") ?>
                        </span>

                        <h3><?= t("home_volunteer_title") ?></h3>

                        <p>
                            <?= t("home_volunteer_text") ?>
                        </p>

                        <a href="candidature-benevole.php" class="nmw-btn nmw-btn-leaf">

                            <span><?= t("home_volunteer_button") ?></span>

                            <i class="fa-solid fa-arrow-right"></i>

                        </a>

                    </article>

                </div>

            </div>

        </div>

    </section>

    <section class="nmw-values">

        <div class="container">

            <div class="nmw-values-panel">

                <div class="nmw-value">

                    <i class="fa-solid fa-seedling"></i>

                    <strong>
                        <?= t("home_preserve") ?>
                    </strong>

                    <span>
                        <?= t("home_preserve_text") ?>
                    </span>

                </div>

                <div class="nmw-value">

                    <i class="fa-solid fa-share-nodes"></i>

                    <strong>
                        <?= t("home_share") ?>
                    </strong>

                    <span>
                        <?= t("home_share_text") ?>
                    </span>

                </div>

                <div class="nmw-value">

                    <i class="fa-solid fa-location-dot"></i>

                    <strong>
                        <?= t("home_local_action") ?>
                    </strong>

                    <span>
                        <?= t("home_local_action_text") ?>
                    </span>

                </div>

            </div>

        </div>

    </section>

</main>

<?php include __DIR__ . "/../includes/footer.php"; ?>