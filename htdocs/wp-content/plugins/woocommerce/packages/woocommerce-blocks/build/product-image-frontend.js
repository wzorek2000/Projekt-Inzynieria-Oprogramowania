(window.webpackWcBlocksJsonp=window.webpackWcBlocksJsonp||[]).push([[61,64],{113:function(e,t,n){"use strict";n.d(t,"a",(function(){return a})),n(101);var c=n(44);const a=()=>c.m>1},133:function(e,t,n){"use strict";n.d(t,"c",(function(){return o})),n.d(t,"d",(function(){return i})),n.d(t,"b",(function(){return u})),n.d(t,"a",(function(){return b}));var c=n(70),a=n(113),r=n(59),s=n(61);const l=e=>Object(r.a)(e)?JSON.parse(e)||{}:Object(s.a)(e)?e:{},o=e=>{if(!Object(a.a)()||"function"!=typeof c.__experimentalGetSpacingClassesAndStyles)return{style:{}};const t=Object(s.a)(e)?e:{},n=l(t.style);return Object(c.__experimentalGetSpacingClassesAndStyles)({...t,style:n})},i=e=>{const t=Object(s.a)(e)?e:{},n=l(t.style),c=Object(s.a)(n.typography)?n.typography:{};return{style:{fontSize:t.fontSize?`var(--wp--preset--font-size--${t.fontSize})`:c.fontSize,lineHeight:c.lineHeight,fontWeight:c.fontWeight,textTransform:c.textTransform,fontFamily:t.fontFamily}}},u=e=>{if(!Object(a.a)())return{className:"",style:{}};const t=Object(s.a)(e)?e:{},n=l(t.style);return Object(c.__experimentalUseColorProps)({...t,style:n})},b=e=>{if(!Object(a.a)())return{className:"",style:{}};const t=Object(s.a)(e)?e:{},n=l(t.style);return Object(c.__experimentalUseBorderProps)({...t,style:n})}},21:function(e,t,n){"use strict";var c=n(0),a=n(4),r=n.n(a);t.a=e=>{let t,{label:n,screenReaderLabel:a,wrapperElement:s,wrapperProps:l={}}=e;const o=null!=n,i=null!=a;return!o&&i?(t=s||"span",l={...l,className:r()(l.className,"screen-reader-text")},Object(c.createElement)(t,l,a)):(t=s||c.Fragment,o&&i&&n!==a?Object(c.createElement)(t,l,Object(c.createElement)("span",{"aria-hidden":"true"},n),Object(c.createElement)("span",{className:"screen-reader-text"},a)):Object(c.createElement)(t,l,n))}},292:function(e,t){},309:function(e,t,n){"use strict";n.r(t);var c=n(0),a=n(1),r=n(4),s=n.n(r),l=n(21),o=n(43),i=n(117),u=(n(292),n(133));t.default=Object(i.withProductDataContext)(e=>{const{className:t,align:n}=e,{parentClassName:r}=Object(o.useInnerBlockLayoutContext)(),{product:i}=Object(o.useProductDataContext)(),b=Object(u.a)(e),d=Object(u.b)(e),m=Object(u.d)(e),p=Object(u.c)(e);if(!i.id||!i.on_sale)return null;const O="string"==typeof n?"wc-block-components-product-sale-badge--align-"+n:"";return Object(c.createElement)("div",{className:s()("wc-block-components-product-sale-badge",t,O,{[r+"__product-onsale"]:r},d.className,b.className),style:{...d.style,...b.style,...m.style,...p.style}},Object(c.createElement)(l.a,{label:Object(a.__)("Sale","woocommerce"),screenReaderLabel:Object(a.__)("Product on sale","woocommerce")}))})},342:function(e,t){},440:function(e,t,n){"use strict";n.r(t);var c=n(117),a=n(11),r=n.n(a),s=n(0),l=n(1),o=n(4),i=n.n(o),u=n(2),b=n(43),d=n(40),m=n(309),p=(n(342),n(133));const O=()=>Object(s.createElement)("img",{src:u.PLACEHOLDER_IMG_SRC,alt:"",width:500,height:500}),j=e=>{let{image:t,onLoad:n,loaded:c,showFullSize:a,fallbackAlt:l}=e;const{thumbnail:o,src:i,srcset:u,sizes:b,alt:d}=t||{},m={alt:d||l,onLoad:n,hidden:!c,src:o,...a&&{src:i,srcSet:u,sizes:b}};return Object(s.createElement)(s.Fragment,null,m.src&&Object(s.createElement)("img",r()({"data-testid":"product-image"},m)),!c&&Object(s.createElement)(O,null))};var f=Object(c.withProductDataContext)(e=>{const{className:t,imageSizing:n="full-size",showProductLink:c=!0,showSaleBadge:a,saleBadgeAlign:r="right"}=e,{parentClassName:o}=Object(b.useInnerBlockLayoutContext)(),{product:u}=Object(b.useProductDataContext)(),[f,g]=Object(s.useState)(!1),{dispatchStoreEvent:y}=Object(d.a)(),h=Object(p.d)(e),w=Object(p.a)(e),_=Object(p.c)(e);if(!u.id)return Object(s.createElement)("div",{className:i()(t,"wc-block-components-product-image",{[o+"__product-image"]:o},w.className),style:{...h.style,...w.style,..._.style}},Object(s.createElement)(O,null));const k=!!u.images.length,E=k?u.images[0]:null,S=c?"a":s.Fragment,N=Object(l.sprintf)(
/* translators: %s is referring to the product name */
Object(l.__)("Link to %s","woocommerce"),u.name),x={href:u.permalink,...!k&&{"aria-label":N},onClick:()=>{y("product-view-link",{product:u})}};return Object(s.createElement)("div",{className:i()(t,"wc-block-components-product-image",{[o+"__product-image"]:o},w.className),style:{...h.style,...w.style,..._.style}},Object(s.createElement)(S,c&&x,!!a&&Object(s.createElement)(m.default,{align:r,product:u}),Object(s.createElement)(j,{fallbackAlt:u.name,image:E,onLoad:()=>g(!0),loaded:f,showFullSize:"cropped"!==n})))});t.default=Object(c.withFilteredAttributes)({showProductLink:{type:"boolean",default:!0},showSaleBadge:{type:"boolean",default:!0},saleBadgeAlign:{type:"string",default:"right"},imageSizing:{type:"string",default:"full-size"},productId:{type:"number",default:0}})(f)},61:function(e,t,n){"use strict";n.d(t,"a",(function(){return c})),n.d(t,"b",(function(){return a}));const c=e=>!(e=>null===e)(e)&&e instanceof Object&&e.constructor===Object;function a(e,t){return c(e)&&t in e}}}]);