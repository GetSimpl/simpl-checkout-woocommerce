export const simpl_containers = document.getElementsByClassName("simpl-checkout-cta-container");

import { displayCTA } from "./helper";
import { showIframeModal } from "./checkoutFrame";
import { addIframe } from "./checkoutFrame";

export const renderWidget = (widgetConfig) => {
  const { button, cta_text, buttons, pages, text_color } = widgetConfig;

  for (let i = 0; i < simpl_containers.length; i++) {
    let current_page = simpl_containers[i].getAttribute("page");
    let background = simpl_containers[i].getAttribute("data-background");
    let button_template = buttons[pages[current_page]];

    displayCTA(simpl_containers[i], button_template, cta_text, background, text_color);
    attachCTAClickEvent(simpl_containers[i]);
  }
};

const attachCTAClickEvent = (simpl_container) => {
  const button_dom = simpl_container.querySelector(`.simpl-button`);

  button_dom.addEventListener("click", (e) => {
    e.preventDefault();
    payClickHandler(simpl_container);
  });
};

const payClickHandler = (simpl_container) => {
  const isCart = window.location.href.includes("cart");

  let productID = simpl_container.getAttribute("data-product-id");
  let quantity = document.getElementsByName("quantity")[0]?.value;
  let variantId = "";

  if (document.getElementsByName("variation_id").length) {
    variantId = document.getElementsByName("variation_id")[0].value;
  }
  console.log({ productID, quantity, variantId, isCart });
  console.log(
    JSON.stringify({ product_id: productID, variant_id: variantId, quantity: quantity, isCart })
  );
  fetch("/wp-json/simpl/v1/cart", {
    method: "POST",
    body: JSON.stringify({
      product_id: parseInt(productID),
      variant_id: parseInt(variantId),
      quantity: parseInt(quantity),
    }),
    headers: { "content-type": "application/json" },
  })
    .then((response) => {
      console.log(response);
      return response.json();
    })
    .then((result) => {
      console.log(result);
      showIframeModal();
      addIframe(result);
    });
};
