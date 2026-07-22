<?php

namespace BitCode\WELZP\Admin\ZohoPeople;

use BitCode\WELZP\Core\Database\ZohoPeoplesEmployeesModel;
use BitCode\WELZP\Core\Database\FormDetailsModel;

final class Hooks
{
    //Reviews shown before the "Read More" button reveals the rest
    private const REVIEWS_VISIBLE = 3;

    private static $_zohoPeoplesEmployeesModel;
    private static $_formDetailsModel;
    private static $_zohoId;

    public function __construct()
    {
        self::$_zohoPeoplesEmployeesModel = new ZohoPeoplesEmployeesModel();
        self::$_formDetailsModel = new FormDetailsModel();
        if (isset($_GET['zoho_id'])) {
            self::$_zohoId = sanitize_text_field(wp_unslash($_GET['zoho_id']));
        }
    }

    public function registerHooks()
    {
        add_shortcode('welz', [$this, 'showReviewForm']);
        add_shortcode('welz-show-all-reviews', [$this, 'showAllReviews']);
        add_shortcode('welz-thank-you-page', [$this, 'thankYouPage']);
    }

    /**
     * Render a review's rating as five inline-SVG stars, filled to match the score.
     *
     * A score of 0 means the rating question was left blank, so nothing is rendered.
     *
     * @param  int|string $star Score, clamped to 0-5.
     * @return string Empty when there is no rating to show.
     */
    private function renderStarRating($star)
    {
        $star = max(0, min(5, (int) $star));
        if ($star < 1) {
            return '';
        }

        $starPath = 'M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z';

        $html = sprintf(
            '<span class="star-rating" role="img" aria-label="%s">',
            esc_attr($star . ' out of 5 stars')
        );
        for ($i = 1; $i <= 5; $i++) {
            $html .= sprintf(
                '<svg class="star%s" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="%s" /></svg>',
                $i <= $star ? ' filled' : '',
                $starPath
            );
        }

        return $html . '</span>';
    }

    public function renderReview($attributes)
    {
        $default = array(
            'type' => 'primary',
            'title' => __("Button", 'philosophy'),
            'url' => '',
        );

        $button_attributes = shortcode_atts($default, $attributes);
        return sprintf(
            '<a target="_blank" class="btn btn--%s full-width" href="%s">%s</a>',
            $button_attributes['type'],
            $button_attributes['url'],
            $button_attributes['title']
        );
    }

    public function showReviewForm()
    {
        $id = static::$_zohoId;
        if ($id === null || $id === '') {
            return '';
        }

        //get() returns a WP_Error when no row matches
        $employeeData = static::$_zohoPeoplesEmployeesModel->get("*", array('zoho_id' => $id), null, null, 'id', 'DESC');
        if (is_wp_error($employeeData) || empty($employeeData)) {
            return '';
        }

        $upload_dir  = wp_upload_dir();
        $employee_name = trim($employeeData[0]->fname . ' ' . $employeeData[0]->lname);
        $headshot_download_url = $employeeData[0]->headshot_download_url;
        $new_headshot_download_url = '';

        if (empty($headshot_download_url)) {
            $new_headshot_download_url = 'https://wellqor.com/wp-content/uploads/2021/11/bioPicplaceholder.jpg';
        } else {
            $new_headshot_download_url = $upload_dir['baseurl'] . "/" . $employeeData[0]->headshot_download_url;
        }

        ob_start(); ?>

        <head>
            <style>
                .profile-heading {
                    height: 250px;
                    display: flex;

                    .container {
                        background-color: #304575;
                        width: 100% !important;
                        display: flex;
                        position: relative;

                        &::before {
                            content: "";
                            position: absolute;
                            width: 240px;
                            height: 240px;
                            top: 5%;
                            right: 3%;
                            background: url(https://wellqor.com/wp-content/uploads/2023/11/WellQor_heart.svg) 0 0 no-repeat;
                            transform: rotate(349deg);
                            opacity: 8%;
                        }

                        .profile-img {
                            margin-left: 40px;
                            margin-top: 40px;
                            position: relative;

                            >img:nth-child(1) {
                                rotate: 90deg;
                                max-width: 300px;
                                height: 280px;
                            }

                            >img:nth-child(2) {
                                width: 250px;
                                height: 260px;
                                border-radius: 100% 100% 100% 0;
                                position: absolute;
                                top: 16px;
                                left: 33px;
                                object-fit: cover;
                                object-position: 17% 9%;
                            }

                        }

                        .title {
                            display: flex;
                            flex-direction: column;
                            height: 100%;
                            color: #fff;
                            justify-content: center;
                            padding-left: 60px;

                            & h2 {
                                font-weight: bold;
                                color: #fff;
                                font-size: 30px;
                            }

                            & p {
                                margin-bottom: 0;
                            }

                            .designation {
                                font-size: 16px;
                            }

                        }
                    }
                }

                .reviews-form {
                    .container {
                        max-width: 52% !important;
                    }

                    display: flex;
                    flex-direction: column;
                    margin-top: 100px;
                    gap: 15px;

                    & h3 {
                        margin-top: 15px;
                        font-size: 30px;
                        font-weight: 600;
                    }

                    & label {
                        margin-right: 0 !important;
                    }

                    & form {
                        display: flex;
                        flex-direction: column;
                        margin-top: 20px;
                        gap: 15px;

                    }

                    .control_indicator {
                        display: flex;
                        gap: 5px;
                    }

                    .rating-title {
                        display: block;
                    }

                    .age-option {
                        display: flex;
                        gap: 4px;
                    }

                    .gender {
                        display: flex;
                        gap: 20px;

                    }

                    .gender-option {
                        display: flex;
                        gap: 4px;
                    }

                    .name {
                        display: flex;

                        & input {
                            margin-top: 15px;
                        }
                    }

                    .form-button {
                        display: flex;
                        gap: 10px;
                        margin-bottom: 30px;
                    }

                }

                input[type="radio"],
                input[type="checkbox"] {
                    margin: 2px 0 0 !important;
                }

                .reviews-form .star-input {
                    width: 0;
                    height: 0;
                    position: absolute;
                    left: -5000px;
                }

                /* .filled lights up every star up to the selected one */
                .reviews-form .star-input-label.filled .orange {
                    animation: enlarge 0.4s ease-in-out forwards;
                }

                .reviews-form .star-input:focus-visible+.star-input-label {
                    outline: 2px solid #345ac2;
                    outline-offset: 2px;
                }

                .reviews-form .star-input-label {
                    display: inline-block;
                    padding: 8px 2px;
                    text-indent: -5000px;
                    line-height: 0;
                    color: #dcdcdc;
                    position: relative;
                    cursor: pointer;
                }

                .reviews-form .star-input-label:hover,
                .reviews-form .star-input-label.hovered {
                    color: #a7a7a7;
                }

                .reviews-form .star-input-label i {
                    display: block;
                    font-size: 20px;
                    line-height: 20px;
                    text-indent: 0;
                    color: #ccc;
                }

                .reviews-form .star-input-label i.orange {
                    position: absolute;
                    display: block;
                    padding: 8px 2px;
                    top: 0;
                    left: 0;
                    text-align: center;
                    width: 100%;
                    color: orange;
                    transform: scale(0);
                }

                @keyframes enlarge {
                    0% {
                        transform: scale(0);
                    }

                    70% {
                        transform: scale(1.25);
                    }

                    100% {
                        transform: scale(1);
                    }

                }



                .reviews-form input[type="text"] {
                    width: 90%;
                    border-radius: 7px;
                    padding: 17px;
                    border: 1px solid #cacaca;
                }

                .reviews-form input[type="text"]::placeholder {
                    color: #747981;
                }

                .reviews-form textarea {
                    width: 90%;
                    border-radius: 7px;
                    padding: 12px;
                    border: 1px solid #cacaca;
                    resize: none;
                }

                .reviews-form textarea:focus {
                    outline: none;
                }

                .reviews-form input[type="text"]:focus {
                    outline: none;
                }

                .reviews-form textarea:hover {
                    border: 1px solid #345ac2;
                }

                .reviews-form input[type="text"]:hover {
                    border: 1px solid #345ac2;
                }

                .reviews-form input[type="email"]:hover {
                    border: 1px solid #345ac2;
                }

                .reviews-form input[type="radio"] {
                    width: 15;
                    height: 15px;
                    border: 4px solid #3367f5;
                }

                .reviews-form input[type="radio"]:after {
                    left: 1px;
                    top: 2px;
                    width: 9px;
                    height: 9px;
                    background: var(--form--color-text);
                }


                .reviews-form .form-button button:last-child {
                    background: #909090;
                    color: #fff;
                }

                .reviews-form .age-range {
                    display: flex;
                    gap: 20px;

                }


                .btn:hover {
                    color: white;
                }


                /*--snackbar--*/
                #snackbar {
                    visibility: hidden;
                    min-width: 250px;
                    margin-left: -125px;
                    background-color: #383838;
                    color: #fff;
                    text-align: center;
                    border-radius: 10px;
                    padding: 13px;
                    position: fixed;
                    z-index: 20000000000;
                    right: 0;
                    bottom: 30px;
                    font-size: 17px;
                }

                #snackbar.show {
                    visibility: visible;
                    -webkit-animation: fadein 0.5s, fadeout 0.5s 2.5s;
                    animation: fadein 0.5s, fadeout 0.5s 2.5s;
                }

                @-webkit-keyframes fadein {
                    from {
                        bottom: 0;
                        opacity: 0;
                    }

                    to {
                        bottom: 30px;
                        opacity: 1;
                    }
                }

                @keyframes fadein {
                    from {
                        bottom: 0;
                        opacity: 0;
                    }

                    to {
                        bottom: 30px;
                        opacity: 1;
                    }
                }

                @-webkit-keyframes fadeout {
                    from {
                        bottom: 30px;
                        opacity: 1;
                    }

                    to {
                        bottom: 0;
                        opacity: 0;
                    }
                }

                @keyframes fadeout {
                    from {
                        bottom: 30px;
                        opacity: 1;
                    }

                    to {
                        bottom: 0;
                        opacity: 0;
                    }
                }

                @media(min-width: 500px) {

                    .reviews-form {
                        .container {
                            margin-left: 50px;
                        }
                    }
                }

                @media(max-width: 500px) {
                    #content {
                        padding-inline-start: 0;
                        padding-inline-end: 0;
                    }

                    .reviews {
                        .profile-heading {
                            .container {
                                &::before {
                                    content: none;
                                }

                                flex-direction: column;

                                .title {
                                    margin-top: 20px;
                                    padding: 0 20px;
                                    color: #292337;

                                    & h2 {
                                        font-size: 26px;
                                        margin-bottom: 0;
                                        color: #292337;
                                    }

                                }

                                .profile-img {
                                    margin: auto;
                                }
                            }
                        }

                        .reviews-form {
                            .container {
                                max-width: 100% !important;
                                padding: 30px 20px;
                            }

                            & h3 {
                                font-size: 24px;
                            }

                            .age-range {
                                display: flex;
                                flex-direction: column;
                                gap: 10px;
                            }

                            .name {
                                flex-direction: column;
                            }
                        }
                    }

                    .designation {
                        font-size: 12px;
                    }
                }
            </style>
        </head>
        <div class="reviews">
            <div class="profile-heading">
                <div class="container">
                    <div class="profile-img">
                        <img src='https://wellqor.com/wp-content/uploads/2023/11/animated_petal_bulletsArtboard-2-copy-7.svg'>
                        <img src="<?php echo esc_url($new_headshot_download_url) ?>">

                    </div>
                    <div class="title">
                        <div class="name">
                            <h2><span><?php echo esc_html($employeeData[0]->fname) ?></span>
                                <span><?php echo esc_html($employeeData[0]->lname) ?>,
                                </span><span><?php echo esc_html($employeeData[0]->medical_qualification) ?></span>
                            </h2>
                        </div>
                        <div class="designation">
                            <?php echo esc_html($employeeData[0]->clinical_title) ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="reviews-form">
                <div class="container">
                    <div class="description">
                        Please help us by providing feedback on your experience with your WellQor provider. Your input will be
                        used to
                        help us improve service, better orient prospective patients, and ensure appropriate matches with
                        therapists.
                        Thank you for entrusting WellQor with your care!
                    </div>
                    <h3>
                        WellQor Provider Review Form
                    </h3>

                    <form>

                        <div class="review-star">
                            <span class="rating-title title-label">Please rate your overall experience with your
                                therapist:</span>
                            <?php for ($star = 1; $star <= 5; $star++) { ?>
                                <input type="radio" class="star-input" name="star" value="<?php echo $star ?>"
                                    id="star-<?php echo $star ?>" onChange="handleStarChange(event)" />
                                <label class="star-input-label" for="star-<?php echo $star ?>"><?php echo $star ?>
                                    <i class="fa fa-star"></i>
                                    <i class="fa fa-star orange"></i>
                                </label>
                            <?php } ?>
                        </div>

                        <span class="title-label">Please select which of the following phrases describe your therapist:</span>
                        <div class="control-group">
                            <?php
                            $phraseOptions = array(
                                'Knowledgeable',
                                'Supportive',
                                'Friendly',
                                'Helpful',
                                'Understanding',
                                'A good fit for me',
                                'Compassionate',
                                'Professional',
                                'Patient',
                                'Flexible',
                                'Competent',
                                'Empathetic',
                            );
                            foreach ($phraseOptions as $phraseOption) {
                                $phraseId = 'phrase-' . sanitize_title($phraseOption);
                                ?>
                                <div class="control_indicator">
                                    <input type="checkbox" name="phrases" value="<?php echo esc_attr($phraseOption) ?>"
                                        id="<?php echo esc_attr($phraseId) ?>" onChange="handleChange(event)" />
                                    <label class="control control--checkbox"
                                        for="<?php echo esc_attr($phraseId) ?>"><?php echo esc_html($phraseOption) ?></label>
                                </div>
                            <?php } ?>
                        </div>

                        <span class="title-label">Please provide a title for your therapist review:</span>
                        <input type="text" name="title" onChange="handleChange(event)" />
                        <span class='title-label'>Please share a few words that capture your experience with your
                            therapist:</span>

                        <textarea name="desc" rows="5" onChange="handleChange(event)"></textarea>
                        <span class="title-label">What age range are you?</span>
                        <div class="age-range">
                            <?php
                            //Values are kept exactly as-is (including the inconsistent spacing) so they stay
                            //comparable with reviews already stored under these labels.
                            $ageOptions = array(
                                'under 20' => 'Under 20',
                                '20-30'    => '20 - 30',
                                '30 - 40'  => '30 - 40',
                                '40 - 60'  => '40 - 60',
                                '60 - 70'  => '60 - 70',
                                '70+'      => '70+',
                            );
                            foreach ($ageOptions as $ageValue => $ageLabel) {
                                $ageId = 'age-' . sanitize_title($ageValue);
                                ?>
                                <div class='age-option'>
                                    <input type="radio" name="age" id="<?php echo esc_attr($ageId) ?>"
                                        value="<?php echo esc_attr($ageValue) ?>" onChange="handleChange(event)">
                                    <label for="<?php echo esc_attr($ageId) ?>"><?php echo esc_html($ageLabel) ?></label>
                                </div>
                            <?php } ?>
                        </div>
                        <span class="title-label">What gender are you?</span>
                        <div class="gender">
                            <?php foreach (array('Male', 'Female', 'Other') as $genderOption) {
                                $genderId = 'gender-' . sanitize_title($genderOption);
                                ?>
                                <div class="gender-option">
                                    <input type="radio" name="gender" id="<?php echo esc_attr($genderId) ?>"
                                        value="<?php echo esc_attr($genderOption) ?>" onChange="handleChange(event)">
                                    <label for="<?php echo esc_attr($genderId) ?>"><?php echo esc_html($genderOption) ?></label>
                                </div>
                            <?php } ?>
                        </div>

                        <div class='name'>
                            <div class='fname'>
                                <label class="title-label" for="reviewer-fname">Please enter your first name:</label>
                                <input type="text" id="reviewer-fname" name="fname" onChange="handleChange(event)" />
                            </div>
                            <div class='lname'>
                                <label class="title-label" for="reviewer-lname">Please enter your last initial:</label>
                                <input type="text" id="reviewer-lname" name="lname" onChange="handleChange(event)" />
                            </div>


                        </div>

                        <div class="form-button">
                            <button class="btn" type="submit" onclick="handleSubmit(event)">Submit</button>
                            <button class="btn" type="reset">
                                Reset
                            </button>
                        </div>
                    </form>
                </div>
                <div id="snackbar"></div>

            </div>


        </div>
        <script>
            (function() {
                //Identity and endpoints are injected server-side
                const zohoId = <?php echo wp_json_encode((string) $id) ?>;
                const employeeName = <?php echo wp_json_encode($employee_name) ?>;
                const saveUrl = <?php echo wp_json_encode(add_query_arg(array(
                                    'action'      => 'bitwelzp_review_data_save',
                                    '_ajax_nonce' => wp_create_nonce('bitcffp_nonce'),
                                ), admin_url('admin-ajax.php'))) ?>;
                const thankYouUrl = <?php echo wp_json_encode(home_url('/thank-you-page/')) ?>;

                let data = {
                    star: 0,
                    phrases: [],
                    title: "",
                    desc: "",
                    age: "",
                    gender: "",
                    fname: "",
                    lname: "",
                    zoho_id: zohoId,
                    employee_name: employeeName
                };

                const starLabels = Array.from(document.querySelectorAll('.star-input-label'));

                const paintStars = (upTo) => {
                    starLabels.forEach((label, index) => {
                        label.classList.toggle('filled', index < upTo);
                    });
                };

                starLabels.forEach((label, index) => {
                    label.addEventListener('mouseover', () => paintStars(index + 1));
                    label.addEventListener('mouseout', () => paintStars(data.star));
                });

                window.handleStarChange = (e) => {
                    data.star = parseInt(e.target.value, 10);
                    paintStars(data.star);
                };

                const handleSnackBar = (message) => {
                    const x = document.getElementById("snackbar");
                    x.textContent = message;
                    x.className = "show";
                    setTimeout(function() {
                        x.className = x.className.replace("show", "");
                    }, 3000);
                };

                window.handleChange = (e) => {
                    const phrasesArrays = [];
                    for (const item of document.getElementsByName('phrases')) {
                        if (item.checked) {
                            phrasesArrays.push(item.value);
                        }
                    }
                    const { name, value } = e.target;

                    data = { ...data, [name]: value };
                    data.phrases = phrasesArrays;
                };

                window.handleSubmit = async (e) => {
                    e.preventDefault();

                    const button = e.target;
                    button.disabled = true;

                    try {
                        //Navigate to the thank-you page only after the save succeeds
                        const res = await fetch(saveUrl, {
                            method: "POST",
                            headers: { "Content-Type": "application/json" },
                            body: JSON.stringify(data)
                        });
                        const payload = await res.json().catch(() => null);
                        if (!res.ok || !payload || payload.success !== true) {
                            throw new Error('Review save rejected');
                        }
                        window.location.href = thankYouUrl + '?zoho_id=' + encodeURIComponent(zohoId);
                    } catch (err) {
                        button.disabled = false;
                        handleSnackBar('Sorry, we could not save your review. Please try again.');
                    }
                };
            })();
        </script>

    <?php
        return ob_get_clean();
    }



    public function showAllReviews()
    {
        $zoho_id = static::$_zohoId;
        if ($zoho_id === null || $zoho_id === '') {
            return '';
        }

        //get() returns a WP_Error when no row matches
        $employeeData = static::$_zohoPeoplesEmployeesModel->get("*", array('zoho_id' => $zoho_id), null, null, 'id', 'DESC');
        if (is_wp_error($employeeData) || empty($employeeData)) {
            return '';
        }

        $getAllReviews = static::$_formDetailsModel->get("*", [], null, null, 'id', 'DESC');
        if (is_wp_error($getAllReviews) || empty($getAllReviews)) {
            $getAllReviews = [];
        }

        $upload_dir  = wp_upload_dir();
        $headshot_download_url = $employeeData[0]->headshot_download_url;
        $new_headshot_download_url = '';

        if (empty($headshot_download_url)) {
            $new_headshot_download_url = 'https://wellqor.com/wp-content/uploads/2021/11/bioPicplaceholder.jpg';
        } else {
            $new_headshot_download_url = $upload_dir['baseurl'] . "/" . $employeeData[0]->headshot_download_url;
        }

        $reviewsData = array();

        foreach ($getAllReviews as $review) {
            $form_details = json_decode($review->form_details);
            if (!is_object($form_details) || !isset($form_details->zoho_id, $form_details->status)) {
                continue;
            }

            if ($zoho_id == $form_details->zoho_id && $form_details->status == 'approved') {
                $form_details->created_at = $review->created_at;
                $reviewsData[] = $form_details;
            }
        }

        $totalVerifiedReviews = count($reviewsData);
        ob_start(); ?>

        <head>
            <style>
                .reviews {
                    color: #292337;
                    font-size: 16px;

                    .profile-heading {
                        height: 250px;
                        display: flex;

                        .container {
                            background-color: #304575;
                            width: 100% !important;
                            display: flex;
                            position: relative;

                            &::before {
                                content: "";
                                position: absolute;
                                width: 240px;
                                height: 240px;
                                top: 5%;
                                right: 3%;
                                background: url(https://wellqor.com/wp-content/uploads/2023/11/WellQor_heart.svg) 0 0 no-repeat;
                                transform: rotate(349deg);
                                opacity: 8%;
                            }

                            .profile-img {
                                margin-left: 40px;
                                margin-top: 40px;
                                position: relative;

                                >img:nth-child(1) {
                                    rotate: 90deg;
                                    max-width: 300px;
                                    height: 280px;
                                }

                                >img:nth-child(2) {
                                    width: 250px;
                                    height: 260px;
                                    border-radius: 100% 100% 100% 0;
                                    position: absolute;
                                    top: 16px;
                                    left: 33px;
                                    object-fit: cover;
                                    object-position: 17% 9%;
                                }

                            }

                            .title {
                                display: flex;
                                flex-direction: column;
                                height: 100%;
                                color: #fff;
                                justify-content: center;
                                padding-left: 60px;

                                & h2 {
                                    font-weight: bold;
                                    color: #fff;
                                    font-size: 30px;
                                }

                                & p {
                                    margin-bottom: 0;
                                }

                                .designation {
                                    font-size: 16px;
                                }

                            }
                        }
                    }



                    .reviews-details {
                        display: flex;
                        flex-direction: column;
                        gap: 15px;
                        padding: 100px 0 0 60px;
                        width: 70%;

                        & h4 {
                            font-weight: bold;
                            margin-bottom: 0;
                        }

                        & h5 {
                            font-weight: bold;
                            margin-bottom: 0;
                        }

                        .verified-reviews {
                            display: flex;
                            flex-direction: column;
                        }
                    }

                    .reviews-accordion {
                        margin-top: 30px;

                        & h4 {
                            margin-top: 20px;
                            ;
                        }
                    }

                    .pharases-desc {
                        display: flex;
                    }

                    .phrases {
                        display: flex;
                        flex-direction: column;
                        min-width: 40%;
                    }

                    .all-reviews {
                        width: 100%;
                        margin: 30px 0;

                        & button {
                            width: 120px;
                            height: 40px;
                            background-color: #7ACDFE;
                            border: none;
                            border-radius: 20px;
                            font-size: 15px;
                            cursor: pointer;
                            transition: all .1s;
                            padding: 0;

                            &:hover {
                                background-color: transparent;
                                border: 2px solid #292337;
                            }
                        }
                    }
                }



                .d-none {
                    display: none;
                }

                /* Flat selectors: these must not depend on CSS nesting support. */
                .star-rating {
                    display: inline-flex;
                    gap: 2px;
                    line-height: 0;
                }

                .star-rating svg {
                    width: 24px;
                    height: 24px;
                    fill: #dcdcdc;
                }

                .star-rating svg.filled {
                    fill: orange;
                }


                @media(max-width: 767px) {

                    .pharases-desc {
                        display: flex;
                        flex-direction: column !important;
                        gap: 20px !important;
                    }

                    .reviews-details {
                        margin-top: 50px;
                    }

                    .profile-heading .title h2 {
                        font-size: 23px;
                    }

                }

                @media(max-width: 500px) {
                    .reviews {
                        .profile-heading {
                            .container {
                                &::before {
                                    content: none;
                                }

                                flex-direction: column;

                                .title {
                                    margin-top: 20px;
                                    padding: 0 20px;
                                    color: #292337;

                                    & h2 {
                                        font-size: 26px;
                                        margin-bottom: 0;
                                        color: #292337;
                                    }
                                }

                                .profile-img {
                                    margin: auto;
                                }
                            }
                        }

                        .reviews-details {
                            padding: 80px 20px 0;
                            width: 100%;

                            .all-reviews {
                                width: 100%;
                                text-align: center;
                                margin-top: 30px;
                            }
                        }
                    }


                }
            </style>
        </head>
        <div class="reviews">
            <div class="profile-heading">
                <div class="container">
                    <div class="profile-img">
                        <img src='https://wellqor.com/wp-content/uploads/2023/11/animated_petal_bulletsArtboard-2-copy-7.svg'>
                        <img src="<?php echo esc_url($new_headshot_download_url) ?>">

                    </div>
                    <div class="title">
                        <div class="name">
                            <h2 style=""><span><?php echo esc_html($employeeData[0]->fname) ?></span>
                                <span><?php echo esc_html($employeeData[0]->lname) ?>,
                                </span><span><?php echo esc_html($employeeData[0]->medical_qualification) ?></span>
                            </h2>
                        </div>
                        <div class="designation">
                            <?php echo esc_html($employeeData[0]->clinical_title) ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="reviews-details">
                <div class="container">


                    <div class="verified-reviews">
                        <h4>Patient Satisfaction</h4>
                        <span><?php echo (int) $totalVerifiedReviews ?> verified
                            reviews</span>

                    </div>
                    <?php foreach ($reviewsData as $reviewIndex => $review) { ?>

                        <div class='reviews-list<?php echo $reviewIndex >= self::REVIEWS_VISIBLE ? ' d-none' : '' ?>'>
                            <div class='reviews-accordion'>
                                <h5><?php echo isset($review->title) ? esc_html($review->title) : '' ?>
                                </h5>
                                <div class='reviewer-info'>
                                    <span><?php echo isset($review->fname) ? esc_html($review->fname) : '' ?> <span><?php echo !empty($review->lname) ? esc_html($review->lname[0]) : '' ?></span></span>,
                                    <span><?php echo isset($review->gender) ? esc_html($review->gender) : '' ?></span>,
                                    <span> <?php echo isset($review->age) ? esc_html($review->age) : '' ?> (Verified) on <?php echo esc_html($review->created_at) ?></span>
                                </div>

                                <?php echo $this->renderStarRating($review->star ?? 0) ?>
                                <h4><span>Review Highlights</span></h4>
                                <div class='pharases-desc'>
                                    <div class="phrases">
                                        <?php foreach ((array) ($review->phrases ?? []) as $phrase) { ?>
                                            <span><?php echo esc_html($phrase) ?></span>
                                        <?php } ?>
                                    </div>
                                    <div class='desc'>
                                        <?php echo isset($review->desc) ? nl2br(esc_html($review->desc)) : '' ?>

                                    </div>
                                </div>
                            </div>

                        </div>
                    <?php } ?>
                    <?php if ($totalVerifiedReviews > self::REVIEWS_VISIBLE) { ?>
                        <div class='all-reviews'>
                            <button type='button' id='read-more-btn'>Read More</button>
                        </div>
                    <?php } ?>
                </div>



            </div>
        </div>
        <script>
            (function() {
                const button = document.getElementById('read-more-btn');
                if (!button) {
                    return;
                }
                button.addEventListener('click', function() {
                    document.querySelectorAll('.reviews-list.d-none').forEach(function(item) {
                        item.classList.remove('d-none');
                    });
                    button.parentNode.removeChild(button);
                });
            })();
        </script>

    <?php
        return ob_get_clean();
    }


    public function thankYouPage()
    {
        $id = static::$_zohoId;
        ob_start(); ?>

        <head>
            <style>
                .thank-you-page {
                    min-height: 700px;
                    display: flex;
                    flex-direction: column;
                    gap: 20px;
                    justify-content: center;
                    align-items: center;
                    background-size: 550px 600px;
                    background-image: url(https://wellqor.com/wp-content/uploads/2020/04/WellQor_iconBG_overlay2.png);
                    background-repeat: no-repeat;
                    background-position: center;

                }

                h1 {
                    text-align: center;
                }

                .thank-you-page img {
                    width: 250px;
                }

                @media(max-width: 767px) {
                    .pharases-desc {
                        display: flex;
                        flex-direction: column !important;
                        gap: 20px !important;
                    }

                    h1 {
                        font-size: 30px;
                    }
                }

                @media(max-width: 500px) {
                    .thank-you-page {
                        background-image: none;
                    }
                }
            </style>
        </head>
        <div class="thank-you-page">

            <img src='https://wellqor.com/wp-content/uploads/2023/11/5tenets.png'>
            <h1>Thank you for providing your feedback!</h1>
            <button class="btn" onclick="thankYouBack('<?php echo esc_js($id) ?>')">
                Back
            </button>
        </div>
        <script>
            const thankYouBack = (id) => {
                window.location.href = 'https://wellqor.com/therapist-review-form/?zoho_id=' + encodeURIComponent(id)
            }
        </script>

<?php
        return ob_get_clean();
    }
}
