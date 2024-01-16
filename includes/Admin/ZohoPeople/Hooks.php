<?php

namespace BitCode\WELZP\Admin\ZohoPeople;

use BitCode\WELZP\Core\Database\ZohoPeoplesEmployeesModel;
use BitCode\WELZP\Core\Database\FormDetailsModel;

final class Hooks
{
    private static $_zohoPeoplesEmployeesModel;
    private static $_formDetailsModel;
    private static $_zohoId;

    public function __construct()
    {
        self::$_zohoPeoplesEmployeesModel = new ZohoPeoplesEmployeesModel();
        self::$_formDetailsModel = new FormDetailsModel();
        if (isset($_GET['zoho_id'])) {
            self::$_zohoId = $_GET['zoho_id'];
        }
    }

    public function registerHooks()
    {
        add_shortcode('welz', [$this, 'showReviewForm']);
        add_shortcode('welz-show-all-reviews', [$this, 'showAllReviews']);
        add_shortcode('welz-thank-you-page', [$this, 'thankYouPage']);
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
        $employeeData = static::$_zohoPeoplesEmployeesModel->get("*", array('zoho_id' => $id), null, null, 'id', 'DESC');
        $upload_dir  = wp_upload_dir();
        $employee_name = $employeeData[0]->fname . '_' . $employeeData[0]->lname;
        $headshot_download_url = $employeeData[0]->headshot_download_url;
        $new_headshot_download_url = '';

        if ($headshot_download_url === '') {
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
                margin-left:50px;
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

        <span class="title-label">Please provide a title for your therapist review:</span>
        <input type="text" name="title" onChange="handleChange(event)" />
        <span class='title-label'>Please share a few words that capture your experience with your therapist:</span>

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
            onclick="handleSubmit(event,<?php echo $employeeData[0]->zoho_id?>,'<?php echo $employee_name?>')">Submit</button>
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
    zoho_id: <?php echo$_GET['zoho_id']?>
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
  
    data = {
      ...data,
      [name]: value
    };
    data.phrases = phrasesArrays

  }


  const handleSubmit = (e, id,employee_name) => {
    e.preventDefault();
    data.employee_name=employee_name.replace("_", " ")
    bodyOptions = {
      method: "POST",
      body: JSON.stringify(data)
    }
    fetch(
        '<?php echo admin_url('admin-ajax.php'); ?>?action=bitwelzp_review_data_save&_ajax_nonce=<?php echo wp_create_nonce('bitcffp_nonce'); ?>',
        bodyOptions)
      .then(res => console.log(res))
     .catch(err=>console.log(err))
    window.location.href = 'https://wellqor.com/thank-you-page/?zoho_id=' + id
  }

</script>

<?php
        return ob_get_clean();
    }



    public function showAllReviews()
    {
        $zoho_id = static::$_zohoId;
        $employeeData = static::$_zohoPeoplesEmployeesModel->get("*", array('zoho_id' => $zoho_id), null, null, 'id', 'DESC');
        $getAllReviews = static::$_formDetailsModel->get("*", [], null, null, 'id', 'DESC');

        $upload_dir  = wp_upload_dir();
        $headshot_download_url = $employeeData[0]->headshot_download_url;
        $new_headshot_download_url = '';

        if ($headshot_download_url === '') {
            $new_headshot_download_url = 'https://wellqor.com/wp-content/uploads/2021/11/bioPicplaceholder.jpg';
        } else {
            $new_headshot_download_url = $upload_dir['baseurl'] . "/" . $employeeData[0]->headshot_download_url;
        }

        $reviewsData = array();
        $totalStars = 0;

        foreach ($getAllReviews as $review) {
            $form_details = json_decode($review->form_details);

            if ($zoho_id == $form_details->zoho_id && $form_details->status == 'approved') {
                $form_details->created_at = $review->created_at;
                array_push($reviewsData, $form_details);
                $totalStars = $totalStars + $form_details->star;
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
              &::before{
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
        <img src="<?php echo $new_headshot_download_url?>
        
        
    ">

      </div>
      <div class="title">
        <div class="name">
          <h2 style=""><span><?php echo $employeeData[0]->fname?></span> <span><?php echo $employeeData[0]->lname?>, </span><span><?php echo $employeeData[0]->medical_qualification?></span>
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

            <img class='' src='https://wellqor.com/wp-content/uploads/2023/11/rating.png' width='137' height='26' />
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
        <button id='read-more-btn' onClick='showAllReviews()'>Read More</button>
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
  <button class="btn" onclick='thankYouBack(<?php echo $id?>)'>
    Back
  </button>
</div>
<script>
  const thankYouBack = (id) => {
    window.location.href = 'https://wellqor.com/therapist-review-form/?zoho_id=' + id
  }
</script>

<?php
      return ob_get_clean();
    }
}
