import { DEFAULT_CHECKOUT_STYLE_SHEET } from "./consts";

export const displayCTA = (simpl_container, button, cta_text = "") => {
  simpl_container.innerHTML = button;
  if (cta_text) {
    simpl_container.querySelector(".simpl-button-text").innerHTML = cta_text;
  }
};

export const addGlobalSyles = () => {
  const styleTag = document.createElement("style");
  styleTag.innerHTML = DEFAULT_CHECKOUT_STYLE_SHEET;
  document.querySelector("body")?.appendChild(styleTag);

  // attaching font in dom ? because it requires div to set font before it loads
  const tempSpan = document.createElement("span");
  tempSpan.innerText = "test";
  tempSpan.style.fontFamily = "Source Sans Pro";
  tempSpan.style.color = "transparent";

  document.querySelector("body")?.appendChild(tempSpan);
};
