function gtmkitLoad(){const d=window.gtmkit_settings.datalayer_name;let a;const n={"wp-block-handpicked-products":1,"wp-block-product-best-sellers":1,"wp-block-product-category":1,"wp-block-product-new":1,"wp-block-product-on-sale":1,"wp-block-products-by-attribute":1,"wp-block-product-tag":1,"wp-block-product-top-rated":1};document.querySelectorAll(".wc-block-grid .wc-block-grid__product").forEach(function(t){var e=t.closest(".wc-block-grid"),i=t.querySelector(".gtmkit_product_data");if(e&&i){var o,r=e.classList;if(r)for(const c in n)r.contains(c)&&((o=JSON.parse(i.getAttribute("data-gtmkit_product_data"))).item_list_name=window.gtmkit_settings.wc.text[c],o.index=n[c],i.setAttribute("data-gtmkit_product_data",JSON.stringify(o)),n[c]++)}});var t=document.querySelectorAll(".gtmkit_product_data");if(t.length){const i=[];let e;t.forEach(function(t){e=JSON.parse(t.getAttribute("data-gtmkit_product_data")),i.push(e)}),window[d].push({ecommerce:null}),window[d].push({event:"view_item_list",ecommerce:{items:i}})}document.addEventListener("click",function(t){t=t.target;let e;if(!t)return!0;if(t.closest(".add_to_cart_button.ajax_add_to_cart:not(.single_add_to_cart_button)"))e="add_to_cart";else{if(!t.closest(".products")&&!t.closest(".wc-block-grid__products")||!t.closest(".add_to_wishlist, .tinvwl_add_to_wishlist_button:not(.tinvwl-product-in-list)"))return!0;e="add_to_wishlist"}t=t.closest(".product,.wc-block-grid__product"),t=t&&t.querySelector(".gtmkit_product_data");if(!t)return!0;t=JSON.parse(t.getAttribute("data-gtmkit_product_data"));t.quantity=1,window[d].push({ecommerce:null}),window[d].push({event:e,ecommerce:{currency:window.gtmkit_data.wc.currency,value:t.price,items:[t]}})}),document.addEventListener("click",function(o){o=o.target;if(!o)return!0;let r,i=o.closest("form.cart");if(!i){let t=o.parentNode;for(;!i&&t;)i=t.querySelector("form.cart"),t=t.parentNode}if(!i)return!0;if(o.closest(".single_add_to_cart_button:not(.disabled,.input-needed)"))r="add_to_cart";else{if(!o.closest(".add_to_wishlist, .tinvwl_add_to_wishlist_button:not(.tinvwl-product-in-list,.disabled-add-wishlist)"))return!0;r="add_to_wishlist"}var o=i.querySelectorAll("[name=variation_id]"),t=i.classList&&i.classList.contains("grouped_form")&&!i.classList.contains("bundle_form");if(o.length){let t=1,e;a&&(o=i.querySelector("[name=quantity]"),a.quantity=o&&o.value||1,t=a.quantity,e=a.price),(a&&"add_to_cart"===r||"add_to_wishlist"===r)&&(window[d].push({ecommerce:null}),window[d].push({event:r,ecommerce:{currency:window.gtmkit_data.wc.currency,value:e*t,items:[a]}}))}else if(t){o=document.querySelectorAll(".grouped_form .gtmkit_product_data");const c=[];let i=0;if(o.forEach(function(t){let e=document.querySelectorAll("input[name=quantity\\["+t.getAttribute("data-gtmkit_product_id")+"\\]]");if(e=Number(e[0].value),0===(e=isNaN(e)?0:e)&&"add_to_cart"===r)return!0;0===e&&"add_to_wishlist"===r&&(e=1);t=JSON.parse(t.getAttribute("data-gtmkit_product_data"));t.quantity=e,c.push(t),i+=t.price*t.quantity}),0===c.length)return!0;window[d].push({ecommerce:null}),window[d].push({event:r,ecommerce:{currency:window.gtmkit_data.wc.currency,value:i,items:c}})}else{t=JSON.parse(i.querySelector("[name=gtmkit_product_data]")&&i.querySelector("[name=gtmkit_product_data]").value);t.quantity=i.querySelector("[name=quantity]")&&i.querySelector("[name=quantity]").value,window[d].push({ecommerce:null}),window[d].push({event:r,ecommerce:{currency:window.gtmkit_data.wc.currency,value:t.price*t.quantity,items:[t]}})}}),document.addEventListener("click",function(t){var t=t.target;return!t||!t.closest(".mini_cart_item a.remove,.product-remove a.remove")||!(t=JSON.parse(t.getAttribute("data-gtmkit_product_data")))||(window[d].push({ecommerce:null}),void window[d].push({event:"remove_from_cart",ecommerce:{items:[t]}}))});document.addEventListener("click",function(t){t=t.target;if(!t.closest(".products .product:not(.product-category) a:not(.add_to_cart_button.ajax_add_to_cart,.add_to_wishlist,.tinvwl_add_to_wishlist_button),.wc-block-grid__products li:not(.product-category) a:not(.add_to_cart_button.ajax_add_to_cart,.add_to_wishlist,.tinvwl_add_to_wishlist_button),.woocommerce-grouped-product-list-item__label a:not(.add_to_wishlist,.tinvwl_add_to_wishlist_button)"))return!0;var t=t.closest(".product,.wc-block-grid__product");let e;return!t||!(e=t.querySelector(".gtmkit_product_data"))||void 0===e.getAttribute("data-gtmkit_product_data")||!(t=JSON.parse(e.getAttribute("data-gtmkit_product_data")))||(window[d].push({ecommerce:null}),void window[d].push({event:"select_item",ecommerce:{items:[t]}}))}),jQuery(document).on("found_variation",function(t,e){if(void 0!==e){t=t.target;if(t.querySelector("[name=gtmkit_product_data]")){var t=JSON.parse(t.querySelector("[name=gtmkit_product_data]")&&t.querySelector("[name=gtmkit_product_data]").value),i=(t.id=t.item_id=window.gtmkit_settings.wc.pid_prefix+e.variation_id,window.gtmkit_settings.wc.use_sku&&e.sku&&""!==e.sku&&(t.id=t.item_id=window.gtmkit_settings.wc.pid_prefix+e.sku),t.price=e.display_price,[]);for(const o in e.attributes)i.push(e.attributes[o]);t.item_variant=i.filter(t=>t).join("|"),a=t,0!==window.gtmkit_settings.wc.view_item.config&&(window[d].push({ecommerce:null}),window[d].push({event:"view_item",ecommerce:{currency:window.gtmkit_data.wc.currency,value:t.price,items:[t]}}))}}})}"loading"===document.readyState?document.addEventListener("DOMContentLoaded",gtmkitLoad):gtmkitLoad();