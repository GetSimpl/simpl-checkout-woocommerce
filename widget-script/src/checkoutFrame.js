import { log } from "./clientLogger";

let loaderInterval;
let bodyEle;
function disableBackgroundScroll() {
  bodyEle?.classList?.add("simpl-offer-disbale-scroll");
}

function constructIframeContainer() {
  if (bodyEle) {
    bodyEle.insertAdjacentHTML(
      "beforeend",
      `<div id="simpl-checkout-modal" arial-modal="true" class="simpl-checkout-modal">
            <div class="simpl-checkout-modal-content">
                <div class="simpl-checkout-container" id="simpl-iframe-container"> 
                    <div class="simpl-loading"></div>
                </div>
            </div>
        </div>`
    );
  }
}

export function addIframe(url) {
  const iframeElement = `<div id="simpl_body_div_checkout">
    <iframe id="simpl-checkout-iframe" class="simpl-checkout-iframe" src="${url}"></iframe>    
    </div>`;

  renderInIframe(iframeElement, true);

  document
    .getElementById("simpl-checkout-iframe")
    ?.addEventListener(
      "load",
      () => (
        log("Simpl performance done loading website : ", performance.now() - window.simplTimer1),
        hideLoader(),
        clearInterval(loaderInterval)
      )
    );
}

function renderInIframe(htmlContent = "", append = false) {
  const iframeContainer = document.getElementById("simpl-iframe-container");
  if (!iframeContainer) {
    // airbrakeError({ msg: "No iframe container found for initiating iframe" });
    // captureSentryError({ msg: "No iframe container found for initiating iframe" });
    return;
  }
  if (!append) iframeContainer.innerHTML = htmlContent;
  else iframeContainer.innerHTML += htmlContent;
}

function enableBackgroundScroll() {
  bodyEle?.classList?.remove("simpl-offer-disbale-scroll");
}

export function hideErrorModal() {
  enableBackgroundScroll();
  const errorModal = document.querySelector("#simpl-checkout-error-modal");
  if (!errorModal) {
    airbrakeError({ msg: "Trying to close error modal which does not exist" });
    captureSentryError({ msg: "Trying to close error modal which does not exist" });
    return;
  }
  errorModal.classList.remove("show-simpl-checkout-modal");
  errorModal.querySelector("#simpl-checkout-error-copy").innerHTML = "";
}

function constructErrorMessageContainer() {
  if (bodyEle) {
    bodyEle.insertAdjacentHTML(
      "beforeend",
      `<div id="simpl-checkout-error-modal" arial-modal="true" class="simpl-checkout-modal">
          <div class="simpl-error-container" id="simpl-error-container">
            <div class="simpl_body_error_div">
            <div id="simpl-checkout-error-copy"></div>
            <button class="close-modal" id="close-simpl-error-modal" aria-label="close error modal">Close</button>
            </div>
          </div>
      </div>`
    );
  }
  document.getElementById("close-simpl-error-modal")?.addEventListener("click", () => {
    hideErrorModal();
    hideLoader();
    hideIframeModalAlongOverlay();
  });
}

export function hideLoader() {
  const loader = document.getElementById("simpl_body_div_loader");
  loader?.remove();
}

export function hideIframeModalAlongOverlay() {
  enableBackgroundScroll();
  const checkoutModal = document.querySelector("#simpl-checkout-modal");
  if (!checkoutModal) {
    airbrakeError({ msg: "No iframe while trying to close it" });
    captureSentryError({ msg: "No iframe while trying to close it" });

    return;
  }
  checkoutModal.classList.remove("show-simpl-checkout-modal");
  checkoutModal.querySelector("#simpl-iframe-container").innerHTML = "";
}

export function showIframeModal(source) {
  disableBackgroundScroll();
  document.querySelector("#simpl-checkout-modal")?.classList.add("show-simpl-checkout-modal");
}

export function initiateCheckoutFrame() {
  bodyEle = document.querySelector("body");
  constructIframeContainer();
  constructErrorMessageContainer();

  document.getElementById("simpl-offer-modal-checkout-button")?.addEventListener("click", () => {
    hideIframeModalAlongOverlay();
  });
}
