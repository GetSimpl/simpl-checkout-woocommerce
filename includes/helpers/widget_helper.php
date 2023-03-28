<?php

function getWidgetConfig()
{
    // api call to get master config goes here.
    // returning only button for now
    define("BUTTON_TEMPLATE", "<style> .simpl-button-1a {padding: 8px;border-radius: 8px;border: none;background: linear-gradient(90deg, #00D1C1 0%, #00D1DC 100%);justify-content: space-between;display: flex;color: white;cursor: pointer;width: 100%;text-align: left;}  .simpl-button-1a .simpl-logo-container { width: 48px; height: 48px;border-radius: 5px;}  .simpl-button-1a .simpl-logo {margin-top: auto;margin-bottom: auto; width: 100%; height: 100%;object-fit: contain;}  .simpl-button-1a .simpl-button-text-group { margin-right: auto; padding-left: 8px; margin-top: auto; margin-bottom: auto;}  .simpl-button-1a .simpl-button-text {font-size: 20px;line-height: 25px; font-weight: 600; margin-bottom: 2px !important;}   .simpl-button-1a .simpl-button-subtext {font-size: 14px;line-height: 17px;font-weight: 400;}  .simpl-button-1a .simpl-button-upi-icons {display: flex;align-items: center;margin-top: auto;margin-bottom: auto;}  .simpl-button-1a .simpl-button-upi-icons img {height: 32px;}  .simpl-button-1a .simpl-upi-more {color: black;font-size: 10px;padding-left: 6px;}  @media only screen and (max-width: 460px) {     .simpl-button-1a .simpl-upi-more{         display: none;     }     .simpl-button-1a .simpl-button-upi-icons .simpl-upi-icons {height: 24px;}      .simpl-button-1a .simpl-button-text {font-size: 16px;line-height: 20px;}       .simpl-button-1a .simpl-button-subtext {font-size: 10px;line-height: 12px;font-weight: 400;}      .simpl-button-1a .simpl-logo-container { width: 32px; height: 32px;}  }  @container simpl-button-container (max-width: 460px) {     .simpl-button-1a .simpl-upi-more{display: none;}      .simpl-button-1a .simpl-button-upi-icons .simpl-upi-icons {height: 24px;}      .simpl-button-1a .simpl-button-text {font-size: 16px;line-height: 20px;}       .simpl-button-1a .simpl-button-subtext {font-size: 10px;line-height: 12px;font-weight: 400;}      .simpl-button-1a .simpl-logo-container { width: 32px; height: 32px;}  }  .simpl-button-container {     container-type: inline-size;     container-name: simpl-button-container; margin: 1rem 0; } </style>  <button id='simpl_buynow-button' class='simpl-button-1a simpl-button'>     <div class='simpl-logo-container'>         <img class='simpl-logo' src='https://simpl-cdn.s3.amazonaws.com/images/checkout/simpl-icon-1.png'>     </div>     <div class='simpl-button-text-group'>         <p class='simpl-buttonText simpl-button-text' style='margin:0' id='simpl-headingText'>PAY via UPI or COD</p>         <p class='simpl-buttonSubText simpl-button-subtext' style='margin:0' id='simpl-subText'>Simpl exclusive discount</p>     </div>     <div class='simpl-button-upi-icons'>         <img class='simpl-upi-icons' src='https://simpl-cdn.s3.amazonaws.com/images/checkout/upi.png'>         <span class='simpl-upi-more'>&amp; more</span>     </div> </button>");
    return BUTTON_TEMPLATE;
}

function updateCtaWithAdminSettings()
{
    ?>
    <script>
        const simpl_containers = document.getElementsByClassName("simpl-checkout-cta-container");
        for (let i = 0; i < simpl_containers.length; i++) {
            let background = simpl_containers[i].getAttribute("data-background");
            let cta_text = simpl_containers[i].getAttribute("data-text");

            if (cta_text) {
                simpl_containers[i].querySelector(".simpl-button-text").innerHTML = cta_text;
            }
            if (background) {
                simpl_containers[i].querySelector(".simpl-button").style.background = background;
            }
        }
    </script>
    <?
}
?>