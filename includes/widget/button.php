<div>
  <button
    id="btn-simpl-btn"
    type="button" data-product-id="<?= $args["product_id"] ?>" onclick = "submitButton(this)"><?= $args["button_text"] ?>
  </button>
</div>
<script>
    function submitButton(element) {
      variantDoc = document.getElementsByName("variation_id")
      variantId = 0
      if(variantDoc.length > 0) {
        variantId = document.getElementsByName("variation_id")[0].value
      }
      productID = element.getAttribute("data-product-id")
      quantity = document.getElementsByName("quantity")[0].value

      fetch("/wp-json/simpl/v1/cart", {method: "POST", body: JSON.stringify({product_id: productID, variant_id: variantId, quantity: quantity}), headers: {'content-type': 'application/json'}})
      .then((response) => response.json())
      .then((result) => {
        window.open(result, "_blank")
      })
    }
</script>  