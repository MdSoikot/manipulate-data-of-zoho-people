<?php

namespace BitCode\WELZP\Admin\ZohoPeople;

use BitCode\WELZP\Core\Database\ZohoPeoplesEmployeesModel;
use BitCode\WELZP\Core\Database\FormDetailsModel;

final class Hooks
{
    private static $_zohoPeoplesEmployeesModel;
    private static $_formDetailsModel;
    private static $_empoyeeId;

    public function __construct()
    {
        self::$_zohoPeoplesEmployeesModel = new ZohoPeoplesEmployeesModel;
        self::$_formDetailsModel = new FormDetailsModel;
        if (isset($_GET['employee_id'])) {
            self::$_empoyeeId = $_GET['employee_id'];
        }
    }

    public function registerHooks()
    {
        add_shortcode('welz', [$this, 'showReviewForm']);
        add_shortcode('welz-show-all-reviews', [$this, 'showAllReviews']);
        add_shortcode('welz-thank-you-page', [$this, 'thankYouPage']);
        // global $shortcode_tags;
    }

    public function renderReview($attributes)
    {
        $default = [
            'type' => 'primary',
            'title'=> __('Button', 'philosophy'),
            'url'  => '',
        ];

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
        $id = static::$_empoyeeId;
        $employeeData = static::$_zohoPeoplesEmployeesModel->get('*', ['employee_id'=>$id], null, null, 'id', 'DESC');
        $upload_dir = wp_upload_dir();
        $headshot_download_url = $employeeData[0]->headshot_download_url;
        $new_headshot_download_url = '';
        if ($headshot_download_url === '') {
            $new_headshot_download_url = 'https://wellqor.com/wp-content/uploads/2021/11/bioPicplaceholder.jpg';
        } else {
            $new_headshot_download_url = $upload_dir['baseurl'] . '/' . $employeeData[0]->headshot_download_url;
        }

        ob_start(); ?>

<head>
  <style>
    * {
      transition: 0.25s ease-in-out;
      box-sizing: border-box;
    }

    h1 {
      text-align: center;
      font-family: helvetica neue, helvetica, arial, sans-serif;
      font-weight: 100;
      font-size: 50px;
      line-height: 50px;
    }


    .reviews .profile-heading .title {
      height: 100%;
      display: flex;
      color: #fff;
      justify-content: center;
    }

    .reviews .title h2 {
      color: #fff;
    }

    .reviews .description {
      color: black;
      font-weight: 500;
      opacity: .9;
    }

    .reviews h3 {
      margin-top: 15px;
    }

    .reviews-form {
      background-size: 550px 600px;
      background-image: url(https://wellqor.com/wp-content/uploads/2020/04/WellQor_iconBG_overlay2.png);
      background-repeat: no-repeat;
      background-position: 551px -1px;
    }

    .reviews-form form {
      display: flex;
      flex-direction: column;
      margin-top: 20px;
      gap: 15px;

    }

    input[type="radio"],
    input[type="checkbox"] {
      margin: 2px 0 0 !important;
    }

    .reviews-form .container {
      max-width: 52% !important;
    }

    .reviews-form label {
      margin-bottom: 0 !important;
    }

    .reviews-form .star-input {
      width: 0;
      height: 0;
      position: absolute;
      left: -5000px;
    }

    .reviews-form .star-input:checked+.star-input-label .orange {
      animation: enlarge 0.5s ease-in-out forwards;
    }

    .reviews-form .star-input:checked+.star-input-label:nth-of-type(2) .orange {
      animation-delay: 0.1s;
    }

    .reviews-form .star-input:checked+.star-input-label:nth-of-type(3) .orange {
      animation-delay: 0.2s;
    }

    .reviews-form .star-input:checked+.star-input-label:nth-of-type(4) .orange {
      animation-delay: 0.3s;
    }

    .reviews-form .star-input:checked+.star-input-label:nth-of-type(5) .orange {
      animation-delay: 0.4s;
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

    .reviews-form {
      display: flex;
      flex-direction: column;
      margin-top: 20px;
      gap: 15px;
    }

    .reviews-form .control_indicator {
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


    .gender-option {
      display: flex;
      gap: 4px;
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


    .reviews-form .form-button {
      width: 85%;
      text-align: left;
      display: flex;
      gap: 10px;
      margin: 14px 0 20px 0;
    }

    .name input {
      margin-top: 15px;
    }

    .reviews-form .form-button button:first-child {
      background: #1286ff;
    }

    .reviews-form .form-button button:last-child {
      background: #909090;
    }

    .reviews-form .age-range {
      display: flex;
      gap: 20px;

    }


    .reviews-form .gender {
      display: flex;
      gap: 20px;

    }

    .btn {
      border: none;
      border-radius: 5px;
      color: #fff;
      cursor: pointer;
      display: inline-block;
      padding: 10px 30px;
    }

    .btn:hover {
      color: white;
    }





    .line {
      height: 1.5px;
      background-color: #e9e1e1;
      margin-bottom: 20px;
    }

    .composer_content .container.fullwidth {
      width: 100% !important;
    }

    #header .container {
      max-width: 1200px !important;
    }



    #content {
      padding-top: 0 !important;
    }

    #content .row br {
      display: none !important;
    }


    .profile-heading {
      height: 100px;
      background-color: #0088cc;
      padding: 20px 0;
      margin-bottom: 30px;
      display: flex;
    }

    .profile-heading .container {
      max-width: 1200px !important;
      display: flex
    }

    .reviews .profile-img img {
      max-width: 150px;
      max-height: 150px;
      border-radius: 15px;
      border: 1px solid #ccc;
    }

    .profile-heading .title {
      display: flex;
      flex-direction: column;
      height: 100%;
      color: #fff;
      justify-content: center;
      padding-left: 15px;
    }

    .profile-heading .title h2 {
      margin: 0;
      font-weight: bold;
      color: #fff;
      font-size: 28px;
    }

    .profile-heading .title span:nth-child(3) {
      font-weight: 400;
    }

    .riview-form .container {
      padding: 0 20px;
      display: flex;
      padding: 20px 0;
      color: #0d1239;
      max-width: 75% !important;
      width: 100%;
    }

    .name {
      display: flex;
    }

    .title-label {
      font-weight: 600;
      opacity: .9;

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

    /*--snackbar--*/




    @media (min-width: 981px) {
      .composer_content .container.fullwidth {
        max-width: 100% !important;
        width: 100% !important;
      }
    }

    @media(max-width: 1300px) {}

    @media(max-width: 1100px) {
      .reviews-form .age-range {
        flex-direction: column;
      }

      .reviews-form {
        background-position: top !important;
        background-size: 487px 480px;
      }
    }

    @media(max-width: 900px) {
      .name {
        flex-direction: column;
      }

    }

    @media(max-width: 980px) {
      .profile-img .lazy {
        max-width: 120px !important;
        max-height: 130px !important;
        margin-top: 10px;
      }

      .profile-heading .container {
        max-width: 90% !important;
        width: 90% !important;
      }

      #header .container {
        max-width: 94% !important;
        width: 94% !important;
      }

      .profile-content .container {
        max-width: 90% !important;
      }

    }

    @media(max-width: 767px) {
      .reviews-form .container {
        max-width: 70% !important;
        margin-top: 20px;
      }

      .profile-heading .title h2 {
        font-size: 23px;
      }

      .composer_content .container.fullwidth {
        max-width: 100% !important;
      }

      .top_wrapper .span12 {
        width: 100% !important;
      }

      .reviews-form {
        margin-top: 50px;
      }
    }

    @media(max-width: 550px) {
      .reviews-form .container {
        max-width: 80% !important;
      }
    }

    @media(max-width: 500px) {
      #header .container {
        max-width: 90% !important;
        width: 90% !important;
      }

      .reviews-form {
        background-image: none !important;
        margin-top: 10px;

      }


      .profile-heading .title h2 {
        font-size: 14px;
      }

      .profile-img .lazy {
        /*         max-width: 104px !important;
        max-height: 93px !important; */
        margin-top: -17px;
      }

      .riview-form .container {
        max-width: 80% !important;
        flex-direction: column;
      }

      .designation {
        font-size: 12px;
      }

    }

    @media(max-width: 400px) {
      #header .span12 {
        width: 100% !important;
      }

      #footer .container {
        max-width: 90% !important;
      }
    }
  </style>
</head>
<div class="reviews">
  <div class="profile-heading">
    <div class="container">
      <div class="profile-img">
        <img src="<?php echo $new_headshot_download_url?>
				  
				  
			">

      </div>
      <div class="title">
        <div class="name">
          <h2><span><?php echo $employeeData[0]->fname?></span>
            <span><?php echo $employeeData[0]->lname?>,
            </span><span><?php echo $employeeData[0]->medical_qualification?></span>
          </h2>
        </div>
        <div class="designation">
          <?php echo $employeeData[0]->clinical_title?>



        </div>
      </div>
    </div>
  </div>
  <div class="reviews-form">
    <div class="container">
      <div class="description">
        Please help us by providing feedback on your experience with your WellQor provider. Your input will be used to
        help us improve service, better orient prospective patients, and ensure appropriate matches with therapists.
        Thank you for entrusting WellQor with your care!
      </div>
      <h3>
        WellQor Provider Review Form
      </h3>

      <form>

        <div class="review-star">
          <span class="rating-title title-label">Please rate your overall experience with your therapist:</span>
          <input type="checkbox" class="star-input" name="star" value=1 id="1" onChange="handleStarChange(event)" />
          <label class="star-input-label" for="1">1
            <i class="fa fa-star"></i>
            <i class="fa fa-star orange"></i>
          </label>
          <input type="checkbox" class="star-input" name="star" value=2 id="2" onChange="handleStarChange(event)" />
          <label class="star-input-label" for="2">2
            <i class="fa fa-star"></i>
            <i class="fa fa-star orange"></i>
          </label>
          <input type="checkbox" class="star-input" name="star" value=3 id="3" onChange="handleStarChange(event)" />
          <label class="star-input-label" for="3">3
            <i class="fa fa-star"></i>
            <i class="fa fa-star orange"></i>
          </label>
          <input type="checkbox" class="star-input" name="star" value=4 id="4" onChange="handleStarChange(event)" />
          <label class="star-input-label" for="4">4
            <i class="fa fa-star"></i>
            <i class="fa fa-star orange"></i>
          </label>
          <input type="checkbox" class="star-input" name="star" value=5 id="5" onChange="handleStarChange(event)" />
          <label class="star-input-label" for="5">5
            <i class="fa fa-star"></i>
            <i class="fa fa-star orange"></i>
          </label>
        </div>

        <span class="title-label">Please select which of the following phrases describe your therapist:</span>
        <div class="control-group">
          <div class="control_indicator">
            <input type="checkbox" name="phrases" id='Knowledgeable' value="Knowledgeable"
              onChange="handleChange(event)" />
            <label class="control control--checkbox">Knowledgeable </label>
          </div>
          <div class="control_indicator">
            <input type="checkbox" name="phrases" value="Supportive" id='Supportive' onChange="handleChange(event)" />
            <label class="control control--checkbox">Supportive </label>
          </div>
          <div class="control_indicator">
            <input type="checkbox" name="phrases" value="Friendly" id='Friendly' onChange="handleChange(event)" />
            <label class="control control--checkbox">Friendly </label>

          </div>
          <div class="control_indicator">
            <input type="checkbox" name="phrases" value="Helpful" id='Helpful' onChange="handleChange(event)" />
            <label class="control control--checkbox">Helpful </label>

          </div>
          <div class="control_indicator">
            <input type="checkbox" name="phrases" value="Understanding" id='Understanding'
              onChange="handleChange(event)" />
            <label class="control control--checkbox">Understanding </label>

          </div>
          <div class="control_indicator">
            <input type="checkbox" name="phrases" value="A good fit for me" id='fit' onChange="handleChange(event)" />
            <label class="control control--checkbox">A good fit for me </label>

          </div>
          <div class="control_indicator">
            <input type="checkbox" name="phrases" value="Compassionate" id='Compassionate'
              onChange="handleChange(event)" />
            <label class="control control--checkbox">Compassionate </label>

          </div>
          <div class="control_indicator">
            <input type="checkbox" name="phrases" value="Professional" id='Professional'
              onChange="handleChange(event)" />
            <label class="control control--checkbox">Professional </label>

          </div>
          <div class="control_indicator">
            <input type="checkbox" name="phrases" value="Patient" id='Patient' onChange="handleChange(event)" />
            <label class="control control--checkbox">Patient </label>

          </div>
          <div class="control_indicator">
            <input type="checkbox" name="phrases" value="Flexible" id='Flexible' onChange="handleChange(event)" />
            <label class="control control--checkbox">Flexible </label>

          </div>
          <div class="control_indicator">
            <input type="checkbox" name="phrases" value="Competent" id='Competent' onChange="handleChange(event)" />
            <label class="control control--checkbox">Competent </label>

          </div>

          <div class="control_indicator">
            <input type="checkbox" name="empathetic" value="empathetic" id='empathetic'
              onChange="handleChange(event)" />
            <label class="control control--checkbox">Empathetic </label>

          </div>
        </div>

        <span class="title-label">Say a few words that capture your experience with your therapist:</span>
        <input type="text" name="title" onChange="handleChange(event)" />
        <span class='title-label'>Please provide some details on your personal experience that might help others:</span>

        <textarea name="desc" rows="5" onChange="handleChange(event)"></textarea>
        <span class="title-label">What age range are you?</span>
        <div class="age-range">
          <div class='age-option'>
            <input type="radio" name="age" value="under 20" onChange="handleChange(event)">
            <label>Under 20</label>
          </div>
          <div class='age-option'>
            <input type="radio" name="age" value="20-30" onChange="handleChange(event)">
            <label>20 - 30</label>

          </div>
          <div class='age-option'>
            <input type="radio" name="age" value="30 - 40" onChange="handleChange(event)">
            <label>30 - 40</label>

          </div>
          <div class='age-option'>
            <input type="radio" name="age" value="40 - 60" onChange="handleChange(event)">
            <label>40 - 60</label>

          </div>
          <div class='age-option'>
            <input type="radio" name="age" value="60 - 70" onChange="handleChange(event)">
            <label>60 - 70</label>

          </div>
          <div class='age-option'>
            <input type="radio" name="age" value="70+" onChange="handleChange(event)">
            <label>70+</label>

          </div>
        </div>
        <span class="title-label">What gender are you?</span>
        <div class="gender">
          <div class="gender-option">
            <input type="radio" name="gender" value="Male" onChange="handleChange(event)">
            <label>Male</label>
          </div>
          <div class="gender-option">
            <input type="radio" name="gender" value="Female" onChange="handleChange(event)">
            <label>Female</label>
          </div>
          <div class="gender-option">
            <input type="radio" name="gender" value="Other" onChange="handleChange(event)">
            <label>Other</label>
          </div>



        </div>

        <div class='name'>
          <div class='fname'>
            <span class="title-label">Please enter your first name:</span>
            <input type="text" name="fname" onChange="handleChange(event)" />
          </div>
          <div class='lname'>
            <span class="title-label">Please enter your last initial:</span>
            <input type="text" name="lname" onChange="handleChange(event)" />
          </div>


        </div>

        <div class="form-button">
          <button class="btn"
            onclick="handleSubmit(event,<?php echo $employeeData[0]->employee_id?>)">Submit</button>
          <button class="btn" type="reset">
            Reset
          </button>
        </div>
      </form>
    </div>
    <div id="snackbar"></div>

  </div>


</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
<script>
  $('.star-input').click(function() {
    $(this).parent([0]).parent()[0].reset();
    var prevStars = $(this).prevAll();
    var nextStars = $(this).nextAll();
    prevStars.attr('checked', true);
    nextStars.attr('checked', false);
    $(this).attr('checked', true);
  });

  $('.star-input-label').on('mouseover', function() {
    var prevStars = $(this).prevAll();
    prevStars.addClass('hovered');
  });
  $('.star-input-label').on('mouseout', function() {
    var prevStars = $(this).prevAll();
    prevStars.removeClass('hovered');
  })


  let data = {
    star: 0,
    phrases: [],
    title: "",
    desc: "",
    age: "",
    gender: "",
    fname: "",
    lname: "",
    status: "pending",
    employee_id: <?php echo$_GET['employee_id']?>
  }

  const handleStarChange = (e) => {
    data.star = parseInt(e.target.value)
  }
  const handleSnackBar = (message) => {
    var x = document.getElementById("snackbar");
    x.innerHTML = message
    x.className = "show";
    setTimeout(function() {
      x.className = x.className.replace("show", "");
    }, 3000);
  }

  const handleChange = (e) => {
    let phrasesArrays = []
    let phrases = document.getElementsByName('phrases')
    for (let item of phrases) {
      if (item.checked) {
        phrasesArrays.push(item.value)
      }
    }
    const {
      name,
      value
    } = e.target
    console.log(typeof(data.star))
    data = {
      ...data,
      [name]: value
    };
    data.phrases = phrasesArrays
    console.log(data)

  }


  const handleSubmit = (e, id) => {
    e.preventDefault();
    bodyOptions = {
      method: "POST",
      body: JSON.stringify(data)
    }
    fetch(
        '<?php echo admin_url('admin-ajax.php'); ?>?action=bitwelzp_review_data_save&_ajax_nonce=<?php echo wp_create_nonce('bitcffp_nonce'); ?>',
        bodyOptions)
      .then(response => console.log(response))
      .then(json => {
        console.log(json)
      })
    // window.location.href = 'https://wellqor.com/thank-you-page/?employee_id=' + id
  }
</script>

<?php
        return ob_get_clean();
    }

    public function showAllReviews()
    {
        $employee_id = static::$_empoyeeId;
        $employeeData = static::$_zohoPeoplesEmployeesModel->get('*', ['employee_id'=>$employee_id], null, null, 'id', 'DESC');
        $getAllReviews = static::$_formDetailsModel->get('*', [], null, null, 'id', 'DESC');

        $upload_dir = wp_upload_dir();
        $headshot_download_url = $employeeData[0]->headshot_download_url;
        $new_headshot_download_url = '';
        if ($headshot_download_url === '') {
            $new_headshot_download_url = 'https://wellqor.com/wp-content/uploads/2021/11/bioPicplaceholder.jpg';
        } else {
            $new_headshot_download_url = $upload_dir['baseurl'] . '/' . $employeeData[0]->headshot_download_url;
        }
        $reviewsData = [];
        //         $phrasesArray='';
        $totalStars = 0;
        foreach ($getAllReviews as $review) {
            $form_details = json_decode($review->form_details);
            if ($employee_id == $form_details->employee_id && $form_details->status == 'approved') {
                $form_details->created_at = $review->created_at;
                array_push($reviewsData, $form_details);
                //                 $phrasesArray=$form_details->phrases;
                $totalStars = $totalStars + $form_details->star;
            }
        }

        $totalVerifiedReviews = count($reviewsData);

        ob_start(); ?>

<head>
  <style>
    * {
      transition: 0.25s ease-in-out;
      box-sizing: border-box;
    }

    h1 {
      text-align: center;
      font-family: helvetica neue, helvetica, arial, sans-serif;
      font-weight: 100;
      font-size: 50px;
      line-height: 50px;
    }


    .reviews .profile-heading .title {
      height: 100%;
      display: flex;
      color: #fff;
      justify-content: center;
    }

    .reviews .title h2 {
      color: #fff;
    }

    .reviews .description {
      color: black;
      font-weight: 500;
      opacity: .9;
    }

    .reviews h3 {
      margin-top: 15px;
    }

    .reviews-details {
      background-size: 550px 600px;
      background-image: url(https://wellqor.com/wp-content/uploads/2020/04/WellQor_iconBG_overlay2.png);
      background-repeat: no-repeat;
      background-position: 551px -1px;
      min-height: 700px;
    }

    .reviews-details .verified-reviews {
      display: flex;
      flex-direction: column;
      margin-bottom: 20px;
    }

    .reviews-details .container {
      max-width: 52% !important;
    }

    .reviews-list {
      margin-bottom: 35px;
    }




    .reviews-details {
      display: flex;
      flex-direction: column;
      margin-top: 20px;
      gap: 15px;
    }

    .reviews-accordion .title {
      margin-bottom: 15px;
    }

    .reviews-accordion h5 {
      color: #2d2a2a;
      font-weight: 600;
      font-size: 19px;
      margin-bottom: 10px;
    }

    .composer_content .container.fullwidth {
      width: 100% !important;
    }

    #header .container {
      max-width: 1200px !important;
    }



    #content {
      padding-top: 0 !important;
    }

    #content .row br {
      display: none !important;
    }


    .profile-heading {
      height: 100px;
      background-color: #0088cc;
      padding: 20px 0;
      margin-bottom: 30px;
      display: flex;
    }

    .profile-heading .container {
      max-width: 75% !important;
      display: flex
    }

    .reviews .profile-img img {
      max-width: 150px;
      max-height: 150px;
      border-radius: 15px;
      border: 1px solid #ccc;
    }

    .profile-heading .title {
      display: flex;
      flex-direction: column;
      height: 100%;
      color: #fff;
      justify-content: center;
      padding-left: 15px;
    }

    .profile-heading .title h2 {
      margin: 0;
      font-weight: bold;
      color: #fff;
      font-size: 28px;
    }

    .profile-heading .title span:nth-child(3) {
      font-weight: 400;
    }

    .pharases-desc {
      display: flex;
      margin-top: 10px;
    }

    .all-reviews {
      float: right;
    }

    .all-reviews button {
      background: linear-gradient(145deg, #0069ff, #097fe6);
      border: none;
      border-radius: 5px;
      padding: 10px 15px;
      color: #fff;
      font-size: 15px;
    }

    .all-reviews button:hover {
      background: linear-gradient(145deg, #0065f5, #005fe6) !important;
    }

    .phrases {
      display: flex;
      flex-direction: column;
      min-width: 40%;
    }

    .d-none {
      display: none;
    }


    @media (min-width: 981px) {
      .composer_content .container.fullwidth {
        max-width: 100% !important;
        width: 100% !important;
      }
    }


    @media(max-width: 1100px) {
      .reviews-details {
        background-position: top !important;
        background-size: 487px 480px;
      }
    }

    @media(max-width: 980px) {
      .profile-img .lazy {
        max-width: 120px !important;
        max-height: 130px !important;
        margin-top: 10px;
      }

      .profile-heading .container {
        max-width: 90% !important;
        width: 90% !important;
      }

      #header .container {
        max-width: 94% !important;
        width: 94% !important;
      }

      .profile-content .container {
        max-width: 90% !important;
      }

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

      .reviews-details .container {
        max-width: 80% !important;
        margin-top: 20px;
      }

      .profile-heading .title h2 {
        font-size: 23px;
      }

      .composer_content .container.fullwidth {
        max-width: 100% !important;
      }

      .top_wrapper .span12 {
        width: 100% !important;
      }
    }

    @media(max-width: 500px) {
      #header .container {
        max-width: 90% !important;
        width: 90% !important;
      }

      .reviews-details {
        background-image: none !important;
        margin-top: 10px;

      }


      .profile-heading .title h2 {
        font-size: 14px;
      }

      .profile-img .lazy {
        /*         max-width: 104px !important;
        max-height: 93px !important; */
        margin-top: -17px;
      }

      .riview-details .container {
        max-width: 80% !important;
        flex-direction: column;
      }

      .designation {
        font-size: 12px;
      }

    }

    @media(max-width: 400px) {
      #header .span12 {
        width: 100% !important;
      }

      #footer .container {
        max-width: 90% !important;
      }
    }
  </style>
</head>
<div class="reviews">
  <div class="profile-heading">
    <div class="container">
      <div class="profile-img">
        <img src="<?php echo $new_headshot_download_url?>
        
        
    ">

      </div>
      <div class="title">
        <div class="name">
          <h2 style=""><span><?php echo $employeeData[0]->preferred_name_nickname?></span> <span><?php echo $employeeData[0]->lname?>, </span><span><?php echo $employeeData[0]->medical_qualification?></span>
          </h2>
        </div>
        <div class="designation">
          <?php echo $employeeData[0]->clinical_title?>



        </div>
      </div>
    </div>
  </div>
  <div class="reviews-details">
    <div class="container">


      <div class="verified-reviews">
        <h4>Patient Satisfaction</span></h4>
        <span><?php echo$totalVerifiedReviews?> verified
          reviews</span>

      </div>
      <?php foreach ($reviewsData as $review) {?>

      <div class='reviews-list'>
        <div class='reviews-accordion'>
          <h5><?php echo $review->title?>
          </h5>
          <div class='reviewer-info'>
            <span><?php echo $review->fname?> <span><?php echo $review->lname[0]?></span></span></span>,
            <span><?php echo $review->gender?></span>,
            <span> <?php echo $review->age?> (Verified) on <?php echo $review->created_at?><span>
          </div>

          <div class='title'>
            <img class='' src='https://wellqor.com/wp-content/uploads/2021/11/stars.png' width='137' height='26' />
          </div>
          <h4><span>Review Highlights</span></h4>
          <div class='pharases-desc'>
            <div class="phrases">
              <?php foreach ($review->phrases as $phrase) {?>
              <span><?php echo $phrase?></span>
              <?php } ?>
            </div>
            <div class='desc' id='desc'>
              <?php echo $review->desc?>

            </div>
          </div>
        </div>

      </div>
      <?php } ?>
      <div class='all-reviews'>
        <button id='show-all-reviews-btn' onClick='showAllReviews()'>Read More</button>
      </div>
    </div>



  </div>
</div>
</div>




<?php
      return ob_get_clean();
    }

    public function thankYouPage()
    {
        $id = static::$_empoyeeId;
        ob_start(); ?>

<head>
  <style>
    * {
      transition: 0.25s ease-in-out;
      box-sizing: border-box;
    }


    .composer_content .container.fullwidth {
      width: 100% !important;
    }

    .composer_content {
      padding-bottom: 0 !important;
    }

    #header .container {
      max-width: 75% !important;
    }



    #content {
      padding-top: 0 !important;
    }

    #content .row br {
      display: none !important;
    }

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

    .thank-you-page .btn {
      background: linear-gradient(145deg, #0069ff, #097fe6);
      padding: 5px 20px;
      border-radius: 8px;
      border: none;
      color: wheat;
    }

    .btn:focus {
      outline: none;
    }

    .btn:hover {
      background: linear-gradient(145deg, #0065f5, #005fe6) !important;
    }


    @media (min-width: 981px) {
      .composer_content .container.fullwidth {
        max-width: 100% !important;
        width: 100% !important;
      }
    }



    @media(max-width: 980px) {
      #header .container {
        max-width: 90% !important;
        width: 90% !important;
      }


    }


    @media(max-width: 767px) {
      .pharases-desc {
        display: flex;
        flex-direction: column !important;
        gap: 20px !important;
      }

      .composer_content .container.fullwidth {
        max-width: 100% !important;
        margin-top: 20px;
      }

      .top_wrapper .span12 {
        width: 100% !important;
      }

      h1 {
        font-size: 30px;
      }
    }

    @media(max-width: 500px) {
      #header .container {
        max-width: 100% !important;
        width: 100% !important;
      }

      .thank-you-page {
        background-image: none;
      }
    }

    @media(max-width: 400px) {
      #header .span12 {
        width: 100% !important;
      }

      #footer .container {
        max-width: 90% !important;
      }
    }
  </style>
</head>
<div class="thank-you-page">

  <img src='https://wellqor.com/wp-content/uploads/2022/01/WellQorLogoColor_RGB.png'>
  <h1>Thank you for providing your feedback!</h1>
  <button class="btn" onclick='thankYouBack(<?php echo $id?>)'>
    Back
  </button>
</div>
<script>
  const thankYouBack = (id) => {
    window.location.href = 'https://wellqor.com/therapist-review-form/?employee_id=' + id
  }
</script>

<?php
      return ob_get_clean();
    }
}
