(()=>{var e,t={472:(e,t,o)=>{"use strict";o.r(t);var r=o(9196);const a=window.wp.blocks;var l=o(1984),n=o(3710);const s=JSON.parse('{"name":"woocommerce/order-confirmation-downloads","version":"1.0.0","title":"Order Downloads","description":"Display links to purchased downloads.","category":"woocommerce","keywords":["WooCommerce"],"supports":{"multiple":false,"align":["wide","full"],"html":false,"typography":{"fontSize":true,"lineHeight":true,"__experimentalFontFamily":true,"__experimentalTextDecoration":true,"__experimentalFontStyle":true,"__experimentalFontWeight":true,"__experimentalLetterSpacing":true,"__experimentalTextTransform":true,"__experimentalDefaultControls":{"fontSize":true}},"color":{"background":true,"text":true,"link":true,"gradients":true,"__experimentalDefaultControls":{"background":true,"text":true}},"spacing":{"padding":true,"margin":true,"__experimentalDefaultControls":{"margin":false,"padding":false}},"__experimentalBorder":{"color":true,"style":true,"width":true,"__experimentalDefaultControls":{"color":true,"style":true,"width":true}},"__experimentalSelector":".wp-block-woocommerce-order-confirmation-totals table"},"attributes":{"align":{"type":"string","default":"wide"},"className":{"type":"string","default":""}},"textdomain":"woocommerce","apiVersion":2,"$schema":"https://schemas.wp.org/trunk/block.json"}'),c=window.wp.blockEditor,i=window.wp.components;var d=o(5736);o(4190);(0,a.registerBlockType)(s,{icon:{src:(0,r.createElement)(l.Z,{icon:n.Z,className:"wc-block-editor-components-block-icon"})},attributes:{...s.attributes},edit:()=>{const e=(0,c.useBlockProps)({className:"wc-block-order-confirmation-downloads"}),{borderBottomColor:t,borderLeftColor:o,borderRightColor:a,borderTopColor:l,borderWidth:n}=e.style,s={borderBottomColor:t,borderLeftColor:o,borderRightColor:a,borderTopColor:l,borderWidth:n};return(0,r.createElement)("div",{...e},(0,r.createElement)(i.Disabled,null,(0,r.createElement)("table",{style:s,cellSpacing:"0",className:"wc-block-order-confirmation-downloads__table"},(0,r.createElement)("thead",null,(0,r.createElement)("tr",null,(0,r.createElement)("th",{className:"download-product"},(0,r.createElement)("span",{className:"nobr"},(0,d.__)("Product","woocommerce"))),(0,r.createElement)("th",{className:"download-remaining"},(0,r.createElement)("span",{className:"nobr"},(0,d.__)("Downloads remaining","woocommerce"))),(0,r.createElement)("th",{className:"download-expires"},(0,r.createElement)("span",{className:"nobr"},(0,d.__)("Expires","woocommerce"))),(0,r.createElement)("th",{className:"download-file"},(0,r.createElement)("span",{className:"nobr"},(0,d.__)("Download","woocommerce"))))),(0,r.createElement)("tbody",null,(0,r.createElement)("tr",null,(0,r.createElement)("td",{className:"download-product","data-title":"Product"},(0,r.createElement)("a",{href:"https://example.com"},(0,d._x)("Test Product","sample product name","woocommerce"))),(0,r.createElement)("td",{className:"download-remaining","data-title":"Downloads remaining"},(0,d._x)("∞","infinite downloads remaining","woocommerce")),(0,r.createElement)("td",{className:"download-expires","data-title":"Expires"},(0,d._x)("Never","download expires","woocommerce")),(0,r.createElement)("td",{className:"download-file","data-title":"Download"},(0,r.createElement)("a",{href:"https://example.com",className:"woocommerce-MyAccount-downloads-file button alt"},(0,d._x)("Test Download","sample download name","woocommerce"))))))))},save:()=>null})},4190:()=>{},9196:e=>{"use strict";e.exports=window.React},9307:e=>{"use strict";e.exports=window.wp.element},5736:e=>{"use strict";e.exports=window.wp.i18n},444:e=>{"use strict";e.exports=window.wp.primitives}},o={};function r(e){var a=o[e];if(void 0!==a)return a.exports;var l=o[e]={exports:{}};return t[e].call(l.exports,l,l.exports,r),l.exports}r.m=t,e=[],r.O=(t,o,a,l)=>{if(!o){var n=1/0;for(d=0;d<e.length;d++){for(var[o,a,l]=e[d],s=!0,c=0;c<o.length;c++)(!1&l||n>=l)&&Object.keys(r.O).every((e=>r.O[e](o[c])))?o.splice(c--,1):(s=!1,l<n&&(n=l));if(s){e.splice(d--,1);var i=a();void 0!==i&&(t=i)}}return t}l=l||0;for(var d=e.length;d>0&&e[d-1][2]>l;d--)e[d]=e[d-1];e[d]=[o,a,l]},r.n=e=>{var t=e&&e.__esModule?()=>e.default:()=>e;return r.d(t,{a:t}),t},r.d=(e,t)=>{for(var o in t)r.o(t,o)&&!r.o(e,o)&&Object.defineProperty(e,o,{enumerable:!0,get:t[o]})},r.o=(e,t)=>Object.prototype.hasOwnProperty.call(e,t),r.r=e=>{"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})},r.j=1866,(()=>{var e={1866:0};r.O.j=t=>0===e[t];var t=(t,o)=>{var a,l,[n,s,c]=o,i=0;if(n.some((t=>0!==e[t]))){for(a in s)r.o(s,a)&&(r.m[a]=s[a]);if(c)var d=c(r)}for(t&&t(o);i<n.length;i++)l=n[i],r.o(e,l)&&e[l]&&e[l][0](),e[l]=0;return r.O(d)},o=self.webpackChunkwebpackWcBlocksJsonp=self.webpackChunkwebpackWcBlocksJsonp||[];o.forEach(t.bind(null,0)),o.push=t.bind(null,o.push.bind(o))})();var a=r.O(void 0,[2869],(()=>r(472)));a=r.O(a),((this.wc=this.wc||{}).blocks=this.wc.blocks||{})["order-confirmation-downloads"]=a})();