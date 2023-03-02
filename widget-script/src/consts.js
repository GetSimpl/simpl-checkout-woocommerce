export const LOADER_HTML = `<div id="simpl_body_div_loader" class="simpl-checkout-modal-loader">
                            <div class="simpl-checkout-modal-loader__header"></div>
                            <div class="simpl-checkout-modal-loader__content">
                              <div class="simpl-loader">
                                <div class="simpl-loader__wrapper simpl-loader-animation">
                                <div class="simpl-loader__container">
                                  <svg class="simpl-logo" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 192 240">
                                  <path fill="#ffffff" fill-rule="evenodd"
                                    d="M165.5 61l24-24-5.3-5.2c-42-42-110.5-42-152.6 0-40.4 40.3-42 105-5 147.2l-24 24 5.2 5.2c42 42 110.5 42 152.6 0 40.4-40.3 42-105 5-147.2zM42 42.2c34.6-34.6 89.8-36.3 126.4-5L154.7 51c-29-24-72.2-22.4-99.3 4.8-27.2 27.2-29 70.2-5 99.4L37 168.6C5.7 132 7.4 76.8 42 42.2zm108 155.6c-34.6 34.6-89.8 36.3-126.4 5L37.3 189c29 24 72.2 22.4 99.3-4.8 27.2-27.2 29-70.2 5-99.4L155 71.4c31.3 36.6 29.6 91.8-5 126.4z" />
                                  </svg>
                                </div>
                                <div class="simpl-loader__spinner">
                                  <svg viewBox="0 0 66 66" xmlns="http://www.w3.org/2000/svg">
                                  <circle class="length" fill="none" stroke-width="3.5" stroke-linecap="round" cx="33" cy="33" r="28" />
                                  </svg>
                                </div>
                                </div>
                                <div class="simpl-loader-animation "></div>
                                <div class="simpl-loader-animation "></div>
                              </div>
                              <span id="simpl-loader__waiting-text">Preparing your order</span>
                              <button id="simpl-loader__try-other-method" class="simpl-loader__try-another-method">Try other checkout</button>
                            </div>
                            <div class="simpl-checkout-modal-loader__footer">
                              <div class="simpl-checkout-modal-loader__footer__powered-by">
                                <div class="bridge-left"></div>
                                <div class="bridge">Powered by <img src="https://cdn.getsimpl.com/tachyon-widget-script-assets/simpl-logo.png" />
                                </div>
                                <div class="bridge-right"></div>
                              </div>
                              <div class="simpl-checkout-modal-loader__footer__trust">
                                <div><img src="https://cdn.getsimpl.com/tachyon-widget-script-assets/rupee.png" />Pay Later</div>
                                <div><img src="https://cdn.getsimpl.com/tachyon-widget-script-assets/strike.png" />Instant Checkout</div>
                                <div><img src="https://cdn.getsimpl.com/tachyon-widget-script-assets/shield.png" />Safe and secure</div>
                              </div>
                            </div>
                            </div>`;

const LOADER_STYLE_SHEET = `
.simpl-loader-animation {
  animation-duration: 1s;
  animation-fill-mode: both;
}

.simpl-loader {
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  width: 100%;
  z-index: 30000;
}

.simpl-loader__wrapper {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  width: 100%;
  z-index: 30000;
  display: flex;
  height: 165px;
  width: 165px;
}

.simpl-loader__container {
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  height: 60px;
  width: 60px;
  background: #00A699;
}

.simpl-logo {
  transform: none;
  width: auto;
  height: 32px;
}

.simpl-loader__spinner {
  /* @apply inset-0 absolute; */
  animation: simpl-contanim 2s linear infinite;
  height: 90px;
  margin: auto;
  position: absolute;
  width: 90px;
  z-index: 1000;
  top: 0px;
  right: 0px;
  bottom: 0px;
  left: 0px;
}

.simpl-logo svg {
  height: 100%;
  transform: rotate(-90deg);
  width: 100%;
}

.simpl-loader__spinner svg:nth-child(1) circle {
  stroke: #00d1c1;
  stroke-dasharray: 1, 300;
  stroke-dashoffset: 0;
  animation: simpl-strokeanim 2s calc(0.2s * (1)) ease infinite;
  transform-origin: center center;
}

.simpl-loader__try-another-method {
  cursor: pointer;
  display: none;
  border: none;
  font-family: 'Source Sans Pro';
  font-style: normal;
  font-weight: 600;
  font-size: 14px;
  line-height: 18px;
  letter-spacing: -0.02em;
  color: #00A699;
  background: none;
}

.simpl-loader__redirection-text {
  font-family: 'Source Sans Pro';
  font-style: normal;
  font-weight: 400;
  font-size: 14px;
  line-height: 18px;
  letter-spacing: -0.02em;
  color: #737373;
}

@keyframes simpl-strokeanim {
  0% {
    stroke-dasharray: 1, 300;
    stroke-dashoffset: 0;
  }

  50% {
    stroke-dasharray: 120, 300;
    stroke-dashoffset: -58.548324585;
  }

  100% {
    stroke-dasharray: 120, 300;
    stroke-dashoffset: -175.6449737549;
  }
}

@keyframes simpl-contanim {
  100% {
    transform: rotate(360deg);
  }
}
.simpl-checkout-modal-loader__footer {
  font-family: 'Source Sans Pro';
  font-style: normal;
  font-weight: 400;
  font-size: 10px;
  line-height: 13px;
  letter-spacing: -0.02em;
  color: #888888;
}

.simpl-checkout-modal-loader__footer__powered-by {
  display: flex;
  justify-content: space-around;
}

.simpl-checkout-modal-loader__footer .bridge-left {
  display: block !important;
  width: 33%;
  border-top: 0.5px solid #E0E0E0;
  margin-top: 6.5px;
}

.simpl-checkout-modal-loader__footer .bridge img {
  margin-top: 1px;
  margin-left: 2px;
}

.simpl-checkout-modal-loader__footer__trust {
  display: flex;
  justify-content: space-between;
  padding: 6px 40px;
}

.simpl-checkout-modal-loader__footer__trust div {
  display: flex;
}

.simpl-checkout-modal-loader__footer .bridge {
  display: flex;
}

.simpl-checkout-modal-loader__footer .bridge-right {
  display: block !important;
  width: 35%;
  border-top: 0.5px solid #E0E0E0;
  margin-top: 6.5px;
}`;

export const DEFAULT_CHECKOUT_STYLE_SHEET = ` 
@font-face {
  font-family: Source Sans Pro;
  src: url(https://cdn.getsimpl.com/tachyon-widget-script-assets/SourceSansPro-Regular.otf);
  font-weight: Regular;
}
.simpl-checkout-modal {
  z-index: 999999999999999999999999999999;
  display: none;
  position: fixed;
  left: 0;
  top: 0;
  width: 100%;
  height: 100%;
  overflow: auto;
  background-color: rgb(0, 0, 0);
  background-color: rgba(0, 0, 0, 0.7);
}

.simpl-checkout-modal-loader {
  width: 100%;
  height: 100%;
  position: absolute;
  background: #e2eeeefa;
  border-radius: 16px;    
  background: #F6F6F6;
  display: flex;
  flex-direction: column;
}

.simpl-checkout-modal-loader__header {
  display: block !important;
  height: 64px;
  background: #FFFFFF;
  border-bottom: 1px solid #E0E0E0;
  border-radius: 16px 16px 0px 0px;
  display: flex;
  justify-content: flex-end;
  align-items: center;
  padding-right: 25px;
}

.simpl-checkout-modal-loader__footer {
  text-align: center;
}

.simpl-checkout-modal-loader__footer__bridge {
  flex: 1 1 auto;
  border: 0.5px solid #E0E0E0;
  transition: all 0.5s ease-in-out;
}

.simpl-checkout-modal-loader__content {
  font-family: 'Source Sans Pro';
  font-style: normal;
  font-weight: 600;
  font-size: 16px;
  line-height: 20px;
  display: flex;
  align-items: center;
  text-align: center;
  letter-spacing: -0.02em;
  color: #00D1C1;
  height: 80%;
  flex-direction: column;
  justify-content: center;
}

.show-simpl-checkout-modal {
  display: block;
}

.simpl-checkout-modal-content {
  margin: auto;
  position: relative;
  padding: 0;
  outline: 0;
  max-width: 800px;
  height: 600px;
  margin-top:100px;
}

.simpl-error-container {
background: white;
position: absolute;
top: 50%;
left: 50%;
transform: translate(-50%, -50%);
padding: 10px 20px;
max-width: 600px;
}

.simpl-checkout-container {
  border-radius: 16px 16px 0px 0px;
  width: 100%;
  height: 100%;
}

.simpl-checkout-container #simpl_body_div_checkout {
  height: 100%;
  width: 100%;
}

.simpl-error-container .simpl_body_error_div{
text-align:center;
}

.simpl-button:hover{
box-shadow: rgb(0 0 0 / 20%) 0px 5px 15px;
}
.simpl-button.simpl-button-disabled {
opacity: 0.5;
cursor: not-allowed !important;
}
.simpl-button.simpl-button-disabled:hover{
box-shadow: none;
}

@media only screen and (max-width: 600px) {
  .simpl-checkout-modal-content {
      width: 100% !important;
      height: 100% !important;
      margin-top: 0%;
  }

  .simpl-checkout-container {
      height: 100% !important;
  }

  #simpl_body_div_checkout {
      height: 100% !important;
      width: 100% !important;
  }

  .simpl_body_error_div{
      height: 50% !important;
  }

  .simpl-checkout-container {
      height: 100% !important;
  }

  .simpl-checkout-iframe {
    border-radius: 0px !important;
  }

}

.simpl-checkout-iframe {
  border: none;
  width: 100%;
  height: 100%;
  border-radius: 16px;
}

.simpl-offer-modal-checkout-button {
  white-space: normal;
}

.simpl-offer-modal-checkout-button:disabled {
  cursor: not-allowed;
  opacity: 0.3;
}

.simpl-offer-modal-checkout-button {
  border: none;
  display: inline-block;
  padding: 8px 16px;
  vertical-align: middle;
  overflow: hidden;
  text-decoration: none;
  color: inherit;
  background-color: inherit;
  text-align: center;
  cursor: pointer;
  white-space: nowrap;
}

.simpl-offer-modal-checkout-button {
  -webkit-touch-callout: none;
  -webkit-user-select: none;
  -khtml-user-select: none;
  -moz-user-select: none;
  -ms-user-select: none;
  user-select: none;
}

.simpl-loading {
height: 0;
width: 0;
padding: 15px;
border: 6px solid #ccc;
border-right-color: #888;
border-radius: 22px;
-webkit-animation: rotate 1s infinite linear;
/* left, top and position just for the demo! */
position: absolute;
left: 50%;
top: 50%;
display: block !important;
}

@-webkit-keyframes rotate {
/* 100% keyframe for  clockwise. 
   use 0% instead for anticlockwise */
100% {
  -webkit-transform: rotate(360deg);
}
}

body.simpl-offer-disbale-scroll {
  overflow: hidden;
}


    ${LOADER_STYLE_SHEET}
    `;
