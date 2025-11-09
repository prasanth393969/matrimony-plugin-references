var filterableGallery = function ($scope, $) {
   var galleryScope = $(".eael-filter-gallery-container", $scope);
   var dataSettings = galleryScope.data("settings");

   //Overlay Transition Duration
   var transition_settings = $(".eael-grid-fg-overlay", $scope);
   var transition_settings_data = transition_settings.data("transition");

   //Adjust the height of each grib box
   document.addEventListener("DOMContentLoaded", function () {
      let imgHeight = document.querySelectorAll(".eael-grid-fg-img");
      imgHeight.forEach((height) => {
         let gridBox = height.closest(".eael-grid-fg-item");
         let shadow = gridBox.querySelector(".eael-grid-fg-box");
         shadow.style.height = height.offsetHeight + "px";
      });
   });

   const lineEq = (y2, y1, x2, x1, currentVal) => {
      // y = mx + b
      var m = (y2 - y1) / (x2 - x1),
         b = y1 - m * x1;
      return m * currentVal + b;
   };

   const getRandomInt = (min, max) =>
      Math.floor(Math.random() * (max - min + 1)) + min;

   const getRandomFloat = (min, max) =>
      (Math.random() * (max - min) + min).toFixed(2);

   const setRange = (obj) => {
      for (let k in obj) {
         if (obj[k] == undefined) {
            obj[k] = [0, 0];
         } else if (typeof obj[k] === "number") {
            obj[k] = [-1 * obj[k], obj[k]];
         }
      }
   };

   const getMousePos = (e) => {
      let posx = 0;
      let posy = 0;
      if (!e) e = window.event;
      if (e.pageX || e.pageY) {
         posx = e.pageX;
         posy = e.pageY;
      } else if (e.clientX || e.clientY) {
         posx =
            e.clientX +
            document.body.scrollLeft +
            document.documentElement.scrollLeft;
         posy =
            e.clientY +
            document.body.scrollTop +
            document.documentElement.scrollTop;
      }
      return { x: posx, y: posy };
   };

   class EAEL_GridFlowItem {
      constructor(el, options) {
         this.DOM = { el: el };

         this.options = {
            image: {
               translation: { x: -10, y: -10, z: 0 },
               rotation: { x: 0, y: 0, z: 0 },
            },
            title: {
               translation: { x: 20, y: 10, z: 0 },
            },
            text: {
               translation: { x: 20, y: 10, z: 0 },
               rotation: { x: 0, y: 0, z: -5 },
            },
            icon: {
               translation: { x: -20, y: 0, z: 0 },
               rotation: { x: 0, y: 0, z: 3 },
            },
            shadow: {
               translation: { x: 20, y: 10, z: 0 },
               rotation: { x: 0, y: 0, z: -2 },
               reverseAnimation: { duration: 2, ease: "Back.easeOut" },
            },
            content: {
               translation: { x: 5, y: 3, z: 0 },
            },
         };
         Object.assign(this.options, options);

         this.DOM.animatable = {};
         this.DOM.animatable.image = this.DOM.el.querySelector(
            ".eael-grid-fg-box__img"
         );
         this.DOM.animatable.title = this.DOM.el.querySelector(
            ".eael-grid-fg-title"
         );
         this.DOM.animatable.text = this.DOM.el.querySelector(
            ".eael-grid-fg-control-name"
         );
         this.DOM.animatable.icon =
            this.DOM.el.querySelector(".eael-grid-fg-icon");
         this.DOM.animatable.shadow = this.DOM.el.querySelector(".box__shadow");
         this.DOM.animatable.content = this.DOM.el.querySelector(
            ".eael-gf-box__content"
         );

         this.initEvents();
      }
      initEvents() {
         let enter = false;
         this.mouseenterFn = () => {
            if (enter) {
               enter = false;
            }
            clearTimeout(this.mousetime);
            this.mousetime = setTimeout(() => (enter = true), 40);
         };

         this.mousemoveFn = (ev) =>
            requestAnimationFrame(() => {
               if (!enter) return;
               this.tilt(ev);
            });
         this.mouseleaveFn = (ev) =>
            requestAnimationFrame(() => {
               if (!enter || !allowTilt) return;
               enter = false;
               clearTimeout(this.mousetime);

               for (let key in this.DOM.animatable) {
                  if (
                     this.DOM.animatable[key] == undefined ||
                     this.options[key] == undefined
                  ) {
                     continue;
                  }
                  gsap.to(
                     this.DOM.animatable[key],
                     this.options[key].reverseAnimation != undefined
                        ? this.options[key].reverseAnimation.duration || 0
                        : 1.5,
                     {
                        ease:
                           this.options[key].reverseAnimation != undefined
                              ? this.options[key].reverseAnimation.ease ||
                                "Power2.easeOut"
                              : "Power2.easeOut",
                        x: 0,
                        y: 0,
                        z: 0,
                        rotationX: 0,
                        rotationY: 0,
                        rotationZ: 0,
                     }
                  );
               }
            });
         this.DOM.el.addEventListener("mouseenter", this.mouseenterFn);
         this.DOM.el.addEventListener("mousemove", this.mousemoveFn);
         this.DOM.el.addEventListener("mouseleave", this.mouseleaveFn);
      }
      tilt(ev) {
         if (!allowTilt) return;
         const mousepos = getMousePos(ev);
         // Document scrolls.
         const docScrolls = {
            left:
               document.body.scrollLeft + document.documentElement.scrollLeft,
            top: document.body.scrollTop + document.documentElement.scrollTop,
         };
         const bounds = this.DOM.el.getBoundingClientRect();
         // Mouse position relative to the main element (this.DOM.el).
         const relmousepos = {
            x: mousepos.x - bounds.left - docScrolls.left,
            y: mousepos.y - bounds.top - docScrolls.top,
         };

         // Movement settings for the animatable elements.
         for (let key in this.DOM.animatable) {
            if (
               this.DOM.animatable[key] == undefined ||
               this.options[key] == undefined
            ) {
               continue;
            }

            let t =
                  this.options[key] != undefined
                     ? this.options[key].translation || { x: 0, y: 0, z: 0 }
                     : { x: 0, y: 0, z: 0 },
               r =
                  this.options[key] != undefined
                     ? this.options[key].rotation || { x: 0, y: 0, z: 0 }
                     : { x: 0, y: 0, z: 0 };

            setRange(t);
            setRange(r);

            const transforms = {
               translation: {
                  x:
                     ((t.x[1] - t.x[0]) / bounds.width) * relmousepos.x +
                     t.x[0],
                  y:
                     ((t.y[1] - t.y[0]) / bounds.height) * relmousepos.y +
                     t.y[0],
                  z:
                     ((t.z[1] - t.z[0]) / bounds.height) * relmousepos.y +
                     t.z[0],
               },
               rotation: {
                  x:
                     ((r.x[1] - r.x[0]) / bounds.height) * relmousepos.y +
                     r.x[0],
                  y:
                     ((r.y[1] - r.y[0]) / bounds.width) * relmousepos.x +
                     r.y[0],
                  z:
                     ((r.z[1] - r.z[0]) / bounds.width) * relmousepos.x +
                     r.z[0],
               },
            };

            gsap.to(this.DOM.animatable[key], 1.5, {
               ease: "Power1.easeOut",
               x: transforms.translation.x,
               y: transforms.translation.y,
               z: transforms.translation.z,
               rotationX: transforms.rotation.x,
               rotationY: transforms.rotation.y,
               rotationZ: transforms.rotation.z,
            });
         }
      }
   }

   class EAEL_GridFlowOverlay {
      constructor() {
         this.DOM = {
            el: document.querySelector(`#overlay-${dataSettings.widget_id}`),
         };
         this.DOM.reveal = this.DOM.el.querySelector(".overlay__reveal");
         this.DOM.items = this.DOM.el.querySelectorAll(".overlay__item");
         this.DOM.close = this.DOM.el.querySelector(".overlay__close");
      }
      show(contentItem) {
         this.contentItem = contentItem;
         this.DOM.el.classList.add("overlay--open");
         // show revealer
         gsap.to(
            this.DOM.reveal,
            transition_settings_data.transition_duration,
            {
               ease: "Power1.easeInOut",
               x: "0%",
               onComplete: () => {
                  // hide scroll
                  document.body.classList.add("preview-open");
                  // show preview
                  this.revealItem(contentItem);
                  // hide revealer
                  gsap.to(
                     this.DOM.reveal,
                     transition_settings_data.transition_duration,
                     {
                        delay: 0.2,
                        ease: "Power3.easeOut",
                        x: "-100%",
                     }
                  );

                  this.DOM.close.style.opacity = 1;
               },
            }
         );
      }
      revealItem() {
         this.contentItem.style.opacity = 1;

         let itemElems = [];
         itemElems.push(this.contentItem.querySelector(".box__shadow"));
         itemElems.push(
            this.contentItem.querySelector(".eael-grid-fg-box__img")
         );
         itemElems.push(this.contentItem.querySelector(".eael-grid-fg-title"));
         itemElems.push(
            this.contentItem.querySelector(".eael-grid-fg-control-name")
         );
         itemElems.push(this.contentItem.querySelector(".eael-grid-fg-icon"));
         itemElems.push(this.contentItem.querySelector(".overlay__content"));

         for (let el of itemElems) {
            if (el == null) continue;
            const bounds = el.getBoundingClientRect();
            const win = {
               width: window.innerWidth,
               height: window.innerHeight,
            };
            gsap.to(
               el,
               lineEq(
                  0.8,
                  1.2,
                  win.width,
                  0,
                  Math.abs(bounds.left + bounds.width - win.width)
               ),
               {
                  ease: "Expo.easeOut",
                  delay: 0.2,
                  startAt: {
                     x: `${lineEq(
                        0,
                        800,
                        win.width,
                        0,
                        Math.abs(bounds.left + bounds.width - win.width)
                     )}`,
                     y: `${lineEq(
                        -100,
                        100,
                        win.height,
                        0,
                        Math.abs(bounds.top + bounds.height - win.height)
                     )}`,
                     rotationZ: `${lineEq(
                        5,
                        30,
                        0,
                        win.width,
                        Math.abs(bounds.left + bounds.width - win.width)
                     )}`,
                  },
                  x: 0,
                  y: 0,
                  rotationZ: 0,
               }
            );
         }
      }
      hide() {
         this.DOM.el.classList.remove("overlay--open");

         // show revealer
         gsap.to(
            this.DOM.reveal,
            transition_settings_data.transition_duration,
            {
               //delay: 0.15,
               ease: "Power3.easeOut",
               x: "0%",
               onComplete: () => {
                  this.DOM.close.style.opacity = 0;
                  // show scroll
                  document.body.classList.remove("preview-open");
                  // hide preview
                  this.contentItem.style.opacity = 0;
                  // hide revealer
                  gsap.to(
                     this.DOM.reveal,
                     transition_settings_data.transition_duration,
                     {
                        delay: 0,
                        ease: "Power3.easeOut",
                        x: "100%",
                     }
                  );
               },
            }
         );
      }
   }

   class EAEL_GridFlowGallery {
      constructor(el) {
         this.DOM = { el: el };
         this.items = [];
         //Check if not null
         if (this.DOM.el) {
            this.initializeItems();
            this.overlay = new EAEL_GridFlowOverlay();
            this.overlay.DOM.close.addEventListener("click", () =>
               this.closeItem()
            );
         }
      }

      initializeItems() {
         Array.from(this.DOM.el.querySelectorAll(".eael-grid__item")).forEach(
            (item) => {
               // Skip if item is already initialized
               if (item.hasAttribute("data-initialized")) {
                  return;
               }

               const itemObj = new EAEL_GridFlowItem(item);
               this.items.push(itemObj);

               if (!item.classList.contains("grid__item--noclick")) {
                  itemObj.DOM.el.addEventListener("click", (ev) => {
                     ev.preventDefault();
                     this.openItem(
                        document.querySelector(item.getAttribute("data-item"))
                     );
                  });
               }

               // Mark item as initialized
               item.setAttribute("data-initialized", "true");
            }
         );
      }

      openItem(contentItem) {
         if (this.isPreviewOpen) return;
         this.isPreviewOpen = true;
         allowTilt = false;
         this.overlay.show(contentItem);
         // "explode" grid..
         for (let item of this.items) {
            for (let key in item.DOM.animatable) {
               const el = item.DOM.animatable[key];
               if (el) {
                  const bounds = el.getBoundingClientRect();

                  let x;
                  let y;
                  const win = {
                     width: window.innerWidth,
                     height: window.innerHeight,
                  };

                  if (
                     bounds.top + bounds.height / 2 <
                     win.height / 2 - win.height * 0.1
                  ) {
                     //x = getRandomInt(-250,-50);
                     //y = getRandomInt(20,100)*-1;
                     x =
                        -1 *
                        lineEq(
                           20,
                           600,
                           0,
                           win.width,
                           Math.abs(bounds.left + bounds.width - win.width)
                        );
                     y =
                        -1 *
                        lineEq(
                           20,
                           600,
                           0,
                           win.width,
                           Math.abs(bounds.left + bounds.width - win.width)
                        );
                  } else if (
                     bounds.top + bounds.height / 2 >
                     win.height / 2 + win.height * 0.1
                  ) {
                     //x = getRandomInt(-250,-50);
                     //y = getRandomInt(20,100);
                     x =
                        -1 *
                        lineEq(
                           20,
                           600,
                           0,
                           win.width,
                           Math.abs(bounds.left + bounds.width - win.width)
                        );
                     y = lineEq(
                        20,
                        600,
                        0,
                        win.width,
                        Math.abs(bounds.left + bounds.width - win.width)
                     );
                  } else {
                     //x = getRandomInt(300,700)*-1;
                     x =
                        -1 *
                        lineEq(
                           10,
                           700,
                           0,
                           win.width,
                           Math.abs(bounds.left + bounds.width - win.width)
                        );
                     y = getRandomInt(-25, 25);
                  }

                  gsap.to(el, 0.4, {
                     ease: "Power3.easeOut",
                     delay: lineEq(
                        0,
                        0.3,
                        0,
                        win.width,
                        Math.abs(bounds.left + bounds.width - win.width)
                     ),
                     x: x,
                     y: y,
                     rotationZ: getRandomInt(-10, 10),
                     opacity: 0,
                  });
               }
            }
         }
      }
      closeItem() {
         if (!this.isPreviewOpen) return;
         this.isPreviewOpen = false;
         this.overlay.hide();

         for (let item of this.items) {
            for (let key in item.DOM.animatable) {
               const el = item.DOM.animatable[key];
               if (el) {
                  const bounds = el.getBoundingClientRect();
                  const win = { width: window.innerWidth };
                  gsap.to(el, 0.6, {
                     ease: "Expo.easeOut",
                     delay:
                        0.55 +
                        lineEq(
                           0,
                           0.2,
                           0,
                           win.width,
                           Math.abs(bounds.left + bounds.width - win.width)
                        ),
                     x: 0,
                     y: 0,
                     rotationZ: 0,
                     opacity: 1,
                  });
               }
            }
         }

         allowTilt = true;

         if (this.DOM.el) {
            gsap.set(this.DOM.el, { pointerEvents: "auto" });
         }
      }
   }

   let allowTilt = true;

   //Initialize the Grid Flow Gallery
   let eael_grid_flow_gallery = document.querySelector(
      `#eael-grid-fg-${dataSettings.widget_id}`
   );
   if (eael_grid_flow_gallery) {
      eael_grid_flow_gallery.eaelGallery = new EAEL_GridFlowGallery(
         eael_grid_flow_gallery
      );
   }

   // Preload all the images in the page..
   imagesLoaded(document.querySelectorAll(".eael-grid-fg-box__img"), () =>
      document.body.classList.remove("loading")
   );

   /** -----------------------
    * Harmonic Gallery
   --------------------------*/
   // Preload images
   const preloadImages = (selector = "img") => {
      return new Promise((resolve) => {
         imagesLoaded(
            document.querySelectorAll(selector),
            { background: true },
            resolve
         );
      });
   };

   const calcWinsize = () => {
      return { width: window.innerWidth, height: window.innerHeight };
   };

   const getScrollValues = () => {
      const supportPageOffset = window.pageXOffset !== undefined;
      const isCSS1Compat = (document.compatMode || "") === "CSS1Compat";
      const x = supportPageOffset
         ? window.pageXOffset
         : isCSS1Compat
         ? document.documentElement.scrollLeft
         : document.body.scrollLeft;
      const y = supportPageOffset
         ? window.pageYOffset
         : isCSS1Compat
         ? document.documentElement.scrollTop
         : document.body.scrollTop;
      return { x, y };
   };

   const wrapLines = (elems, wrapType, wrapClass) => {
      elems.forEach((char) => {
         const wrapEl = document.createElement(wrapType);
         wrapEl.classList = wrapClass;
         char.parentNode.appendChild(wrapEl);
         wrapEl.appendChild(char);
      });
   };

   const adjustedBoundingRect = (el) => {
      var rect = el.getBoundingClientRect();
      var style = getComputedStyle(el);
      var tx = style.transform;

      if (tx) {
         var sx, sy, dx, dy;
         if (tx.startsWith("matrix3d(")) {
            var ta = tx.slice(9, -1).split(/, /);
            sx = +ta[0];
            sy = +ta[5];
            dx = +ta[12];
            dy = +ta[13];
         } else if (tx.startsWith("matrix(")) {
            var ta = tx.slice(7, -1).split(/, /);
            sx = +ta[0];
            sy = +ta[3];
            dx = +ta[4];
            dy = +ta[5];
         } else {
            return rect;
         }

         var to = style.transformOrigin;
         var x = rect.x - dx - (1 - sx) * parseFloat(to);
         var y =
            rect.y - dy - (1 - sy) * parseFloat(to.slice(to.indexOf(" ") + 1));
         var w = sx ? rect.width / sx : el.offsetWidth;
         var h = sy ? rect.height / sy : el.offsetHeight;
         return {
            x: x,
            y: y,
            width: w,
            height: h,
            top: y,
            right: x + w,
            bottom: y + h,
            left: x,
         };
      } else {
         return rect;
      }
   };

   /**
    * Class TextReveal
    */
   class TextReveal {
      constructor(el) {
         this.DOM = {
            outer: el,
            inner: Array.isArray(el)
               ? el.map((outer) => outer.querySelector(".eael-split-oh__inner"))
               : el.querySelector(".eael-split-oh__inner"),
         };
      }
      in() {
         if (this.outTimeline && this.outTimeline.isActive()) {
            this.outTimeline.kill();
         }

         this.inTimeline = gsap
            .timeline({ defaults: { duration: 1.2, ease: "expo" } })
            .set(this.DOM.inner, {
               y: "120%",
               rotate: 15,
            })
            .to(this.DOM.inner, {
               y: "0%",
               rotate: 0,
               stagger: 0.03,
            });
         return this.inTimeline;
      }
      out() {
         if (this.inTimeline && this.inTimeline.isActive()) {
            this.inTimeline.kill();
         }

         this.outTimeline = gsap
            .timeline({ defaults: { duration: 0.5, ease: "expo.in" } })
            .to(this.DOM.inner, {
               y: "-120%",
               rotate: -5,
               stagger: 0.03,
            });
         return this.outTimeline;
      }
   }

   /**
    * Class TextLinesReveal
    */
   class TextLinesReveal {
      constructor(animationElems) {
         this.DOM = {
            animationElems: Array.isArray(animationElems)
               ? animationElems
               : [animationElems],
         };

         // array of SplitType instances
         this.SplitTypeInstances = [];
         // array of all HTML .line
         this.lines = [];

         for (const el of this.DOM.animationElems) {
            const SplitTypeInstance = new SplitType(el, { types: "lines" });
            // wrap the lines (div with class .oh)
            // the inner child will be the one animating the transform
            wrapLines(SplitTypeInstance.lines, "div", "eael-split-oh");
            this.lines.push(SplitTypeInstance.lines);
            // keep a reference to the SplitType instance
            this.SplitTypeInstances.push(SplitTypeInstance);
         }

         this.initEvents();
      }
      in() {
         // lines are visible
         this.isVisible = true;

         // animation
         gsap.killTweensOf(this.lines);
         return gsap
            .timeline({ defaults: { duration: 1.2, ease: "expo" } })
            .set(this.lines, {
               y: "150%",
               rotate: 15,
            })
            .to(this.lines, {
               y: "0%",
               rotate: 0,
               stagger: 0.04,
            });
      }
      out() {
         // lines are invisible
         this.isVisible = false;

         // animation
         gsap.killTweensOf(this.lines);
         return gsap
            .timeline({
               defaults: { duration: 0.5, ease: "expo.in" },
            })
            .to(this.lines, {
               y: "-150%",
               rotate: -5,
               stagger: 0.02,
            });
      }
      initEvents() {
         window.addEventListener("resize", () => {
            // empty the lines array
            this.lines = [];
            // re initialize the Split Text
            for (const instance of this.SplitTypeInstances) {
               // re-split text
               // https://github.com/lukePeavey/SplitType#instancesplitoptions-void
               instance.split();

               // need to wrap again the new lines elements (div with class .oh)
               wrapLines(instance.lines, "div", "eael-split-oh");
               this.lines.push(instance.lines);
            }
            // hide the lines
            if (!this.isVisible) {
               gsap.set(this.lines, { y: "-150%" });
            }
         });
      }
   }

   /**
    * Class representing a content item (.content__item).
    */
   class ContentItem {
      // DOM elements
      DOM = {
         // Main element (.content__item)
         el: null,
      };
      // TextReveal obj to animate the texts (slide in/out)
      textReveal = null;
      // TextLinesReveal obj to animate the ,ulti line texts (slide in/out)
      textLinesReveal = null;

      /**
       * Constructor.
       * @param {Element} DOM_el - the .content__item element.
       */
      constructor(DOM_el) {
         this.DOM.el = DOM_el;
         this.DOM.nav = {
            prev: this.DOM.el.querySelector(".slide-nav__img--prev"),
            next: this.DOM.el.querySelector(".slide-nav__img--next"),
         };

         // Text animations
         this.textReveal = new TextReveal([
            ...this.DOM.el.querySelectorAll(".eael-split-oh"),
         ]);
         // Text lines animations
         this.textLinesReveal = new TextLinesReveal(
            this.DOM.el.querySelector(".eael-hg-content__item-text")
         );
      }
   }

   /**
    * Class representing a image cell (.grid__cell-img).
    */
   class ImageCell {
      // DOM elements
      DOM = {
         // Main element (.grid__cell-img)
         el: null,
         // Inner element
         inner: null,
         // The ImageCell's content item id.
         contentId: null,
         // The ContentItem instance
         contentItem: null,
      };

      /**
       * Constructor.
       * @param {Element} DOM_el - the .grid__cell-img element.
       */
      constructor(DOM_el) {
         this.DOM.el = DOM_el;
         this.DOM.inner = this.DOM.el.querySelector(".grid__cell-img-inner");

         // The ImageCell's content item id.
         this.contentId = this.DOM.inner.dataset.item;
         // The ContentItem instance
         this.contentItem = new ContentItem(
            document.querySelector(`#${this.contentId}`)
         );
      }
   }

   // body element
   const bodyEl = document.body;

   // Calculate the viewport size
   let winsize = calcWinsize();
   window.addEventListener("resize", () => (winsize = calcWinsize()));

   /**
    * Class representing a grid of images, where the grid can be zoomed to the clicked image cell
    */
   class Harmonic_Gallery {
      DOM = {
         el: null,
         imageCells: null,
         content: null,
         backCtrl: null,
         miniGrid: {
            el: null,
            cells: null,
         },
      };
      imageCellArr = [];
      currentCell = -1;
      isGridView = true;
      isAnimating = false;
      textReveal = null;

      constructor(DOM_el) {
         this.DOM.el = DOM_el;
         this.initializeGallery();
      }

      initializeGallery() {
         this.initializeItems();
         this.initializeContent();
         this.initializeMiniGrid();
         this.initializeTextAnimations();
         this.initEvents();
      }

      initializeItems() {
         // Get all current image cells
         const allImageCells = this.DOM.el.querySelectorAll(
            ".eael-hg-grid__cell-img"
         );

         // Update the DOM.imageCells array
         this.DOM.imageCells = [...allImageCells];

         // Only add new items that haven't been initialized
         allImageCells.forEach((el) => {
            if (!el.hasAttribute("data-initialized")) {
               this.imageCellArr.push(new ImageCell(el));
               el.setAttribute("data-initialized", "true");
            }
         });
      }

      initializeContent() {
         this.DOM.content = document.querySelector(
            `#eael-hg-content-${dataSettings.widget_id}`
         );

         if (this.DOM.content) {
            this.DOM.backCtrl = this.DOM.content.querySelector(".eael-hg-back");
         }
      }

      initializeMiniGrid() {
         if (this.DOM.content) {
            this.DOM.miniGrid.el = this.DOM.content.querySelector(
               ".eael-hg-grid--mini"
            );

            if (this.DOM.miniGrid.el) {
               this.DOM.miniGrid.cells = [
                  ...this.DOM.miniGrid.el.querySelectorAll(
                     ".eael-hg-grid__cell"
                  ),
               ];

               // Initialize miniGrid cells if they exist
               if (
                  this.DOM.miniGrid.cells &&
                  this.DOM.miniGrid.cells.length > 0
               ) {
                  this.DOM.miniGrid.cells.forEach((cell, position) => {
                     cell.addEventListener("click", () => {
                        if (this.isAnimating || this.currentCell === position) {
                           return false;
                        }
                        this.isAnimating = true;
                        this.changeContent(position);
                     });
                  });
               }
            }
         }
      }

      initializeTextAnimations() {
         const textElements = this.DOM.el.querySelectorAll(".eael-split-oh");
         if (textElements.length > 0) {
            this.textReveal = new TextReveal([...textElements]);
         } else {
            this.textReveal = null;
         }
      }

      /**
       * Track which cells are visible (inside the viewport)
       * by adding/removing the 'in-view' class when scrolling.
       * This will be used to animate only the ones that are visible.
       */
      trackVisibleCells() {
         const observer = new IntersectionObserver((entries, observer) => {
            entries.forEach((entry) => {
               if (entry.intersectionRatio > 0) {
                  entry.target.classList.add("in-view");
               } else {
                  entry.target.classList.remove("in-view");
               }
            });
         });
         this.DOM.imageCells.forEach((img) => observer.observe(img));
      }

      /**
       * Init/Bind events.
       */
      initEvents() {
         // for every imageCell
         for (const [position, imageCell] of this.imageCellArr.entries()) {
            // Open the imageCell and reveal its content
            imageCell.DOM.el.addEventListener("click", () => {
               if (!this.isGridView || this.isAnimating) {
                  return false;
               }
               this.isAnimating = true;
               this.isGridView = false;

               // Update the mini grid current cell
               if (
                  this.currentCell !== -1 &&
                  this.DOM.miniGrid &&
                  this.DOM.miniGrid.cells &&
                  this.DOM.miniGrid.cells[this.currentCell]
               ) {
                  this.DOM.miniGrid.cells[this.currentCell].classList.remove(
                     "grid__cell--current"
                  );
               }

               // Update currentCell
               this.currentCell = position;

               // Add current class to new cell if miniGrid exists
               if (
                  this.DOM.miniGrid &&
                  this.DOM.miniGrid.cells &&
                  this.DOM.miniGrid.cells[this.currentCell]
               ) {
                  this.DOM.miniGrid.cells[this.currentCell].classList.add(
                     "grid__cell--current"
                  );
               }

               this.showContent(imageCell);
            });

            // Hover on the image cell will scale down the outer element and scale up the inner element.
            imageCell.DOM.el.addEventListener("mouseenter", () => {
               if (!this.isGridView) {
                  return false;
               }
               gsap.killTweensOf([imageCell.DOM.el, imageCell.DOM.inner]);
               gsap
                  .timeline({
                     defaults: { duration: 2.4, ease: "expo" },
                  })
                  .to(imageCell.DOM.el, { scale: 0.95 }, 0)
                  .to(imageCell.DOM.inner, { scale: 1.4 }, 0);
            });

            // Hovering out will reverse the scale values.
            imageCell.DOM.el.addEventListener("mouseleave", () => {
               if (!this.isGridView) {
                  return false;
               }
               gsap.killTweensOf([imageCell.DOM.el, imageCell.DOM.inner]);
               gsap
                  .timeline({
                     defaults: { duration: 2.4, ease: "expo" },
                  })
                  .to([imageCell.DOM.el, imageCell.DOM.inner], { scale: 1 }, 0);
            });
         }

         // Close the imageCell and reveal the grid
         if (this.DOM.backCtrl) {
            this.DOM.backCtrl.addEventListener("click", () => {
               if (this.isAnimating) {
                  return false;
               }
               this.isAnimating = true;
               this.isGridView = true;

               this.closeContent();
            });
         }
      }

      /**
       * Scale up the image and reveal its content.
       * @param {ImageCell} imageCell - the imageCell element.
       */
      showContent(imageCell) {
         if (!imageCell) return;
         // Calculate the transform to apply to the image cell
         const imageTransform = this.calcTransformImage();
         // All the others (that are inside the viewport)
         this.otherImageCells = this.DOM.imageCells.filter(
            (el) => el != imageCell.DOM.el
         );

         gsap.killTweensOf([
            imageCell.DOM.el,
            imageCell.DOM.inner,
            this.otherImageCells,
         ]);
         gsap
            .timeline({
               defaults: {
                  duration: 1.2,
                  ease: "expo.inOut",
               },
               // overflow hidden
               onStart: () => bodyEl.classList.add("eael-split-oh"),
               onComplete: () => {
                  this.isAnimating = false;
               },
            })
            .addLabel("start", 0)
            .add(() => {
               // Hide grid texts
               if (this.textReveal) {
                  this.textReveal.out();
               }
            }, "start")
            .set(
               this.DOM.el,
               {
                  pointerEvents: "none",
               },
               "start"
            )
            .set(
               imageCell.DOM.el,
               {
                  zIndex: 1001,
               },
               "start"
            )
            .set(
               [imageCell.DOM.el, imageCell.DOM.inner, this.otherImageCells],
               {
                  willChange: "transform, opacity",
               },
               "start"
            )
            .to(
               imageCell.DOM.el,
               {
                  scale: imageTransform.scale, // 2.88
                  // scale: 2.5, // 2.88
                  x: imageTransform.x, // 668
                  y: imageTransform.y, // 57.6094
                  onComplete: () =>
                     gsap.set(imageCell.DOM.el, { willChange: "" }),
               },
               "start"
            )
            .to(
               imageCell.DOM.inner,
               {
                  scale: 1,
                  onComplete: () =>
                     gsap.set(imageCell.DOM.inner, { willChange: "" }),
               },
               "start"
            )
            .to(
               [
                  imageCell.contentItem.DOM.nav.prev,
                  imageCell.contentItem.DOM.nav.next,
               ],
               {
                  y: 0,
               },
               "start"
            )
            .to(
               this.otherImageCells,
               {
                  opacity: 0,
                  scale: 0.8,
                  onComplete: () =>
                     gsap.set(this.otherImageCells, { willChange: "" }),
                  stagger: {
                     grid: "auto",
                     amount: 0.17,
                     from: this.currentCell,
                  },
               },
               "start"
            )
            .addLabel("showContent", "start+=0.45")
            .to(
               this.DOM.backCtrl,
               {
                  ease: "expo",
                  startAt: { x: "50%" },
                  x: "0%",
                  opacity: 1,
               },
               "showContent"
            )
            .set(
               this.DOM.miniGrid.el,
               {
                  opacity: 1,
               },
               "showContent"
            )
            .set(
               this.DOM.miniGrid.cells,
               {
                  opacity: 0,
               },
               "showContent"
            )
            .to(
               this.DOM.miniGrid.cells,
               {
                  duration: 1,
                  ease: "expo",
                  opacity: 1,
                  startAt: { scale: 0.8 },
                  scale: 1,
                  stagger: {
                     grid: "auto",
                     amount: 0.3,
                     from: this.currentCell,
                  },
               },
               "showContent+=0.2"
            )
            .add(() => {
               imageCell.contentItem.textReveal.in();
               imageCell.contentItem.textLinesReveal.in();
               this.DOM.content.classList.add("content--open");
            }, "showContent")
            .add(
               () =>
                  imageCell.contentItem.DOM.el.classList.add(
                     "content__item--current"
                  ),
               "showContent+=0.02"
            );
      }

      /**
       * Scale down the image and reveal the grid again.
       */
      closeContent() {
         // Current imageCell
         const imageCell = this.imageCellArr[this.currentCell];
         this.otherImageCells = this.DOM.imageCells.filter(
            (el) => el != imageCell.DOM.el
         );

         gsap
            .timeline({
               defaults: {
                  duration: 1,
                  ease: "expo.inOut",
               },
               // overflow hidden
               onStart: () => {
                  bodyEl.classList.remove("eael-split-oh");
                  // Add fade-out animation before removing the class
                  gsap.to(imageCell.contentItem.DOM.el, {
                     duration: 0.2,
                     ease: "power2.out",
                     onComplete: () => {
                        imageCell.contentItem.DOM.el.classList.remove(
                           "content__item--current"
                        );
                        gsap.set(imageCell.contentItem.DOM.el, {
                           y: 0,
                        });
                     },
                  });
               },
               onComplete: () => {
                  this.isAnimating = false;
               },
            })
            .addLabel("start", 0)
            .to(
               this.DOM.backCtrl,
               {
                  x: "50%",
                  opacity: 0,
               },
               "start"
            )
            .to(
               this.DOM.miniGrid.cells,
               {
                  duration: 0.5,
                  ease: "expo.in",
                  opacity: 0,
                  scale: 0.8,
                  stagger: {
                     grid: "auto",
                     amount: 0.1,
                     from: -this.currentCell,
                  },
                  onComplete: () => {
                     if (this.DOM.miniGrid.el) {
                        gsap.set(this.DOM.miniGrid.el, { opacity: 0 });
                     }
                  },
               },
               "start"
            )

            .add(() => {
               if (this.textReveal) {
                  this.textReveal.out();
               }
               if (this.DOM.content) {
                  this.DOM.content.classList.remove("content--open");
               }
            }, "start")
            .addLabel("showGrid", 0)
            .set(
               [imageCell.DOM.el, this.otherImageCells],
               {
                  willChange: "transform, opacity",
               },
               "showGrid"
            )
            .to(
               imageCell.DOM.el,
               {
                  scale: 1,
                  x: 0,
                  y: 0,
                  onComplete: () =>
                     gsap.set(imageCell.DOM.el, { willChange: "", zIndex: 1 }),
               },
               "showGrid"
            )
            .to(
               imageCell.contentItem.DOM.nav.prev,
               {
                  y: "-100%",
               },
               "showGrid"
            )
            .to(
               imageCell.contentItem.DOM.nav.next,
               {
                  y: "100%",
               },
               "showGrid"
            )
            .to(
               this.otherImageCells,
               {
                  opacity: 1,
                  scale: 1,
                  onComplete: () => {
                     gsap.set(this.otherImageCells, { willChange: "" });
                     gsap.set(this.DOM.el, { pointerEvents: "auto" });
                  },
                  stagger: {
                     grid: "auto",
                     amount: 0.17,
                     from: -this.currentCell,
                  },
               },
               "showGrid"
            )
            .add(() => {
               // Show grid texts
               if (this.textReveal) {
                  this.textReveal.in();
               }
            }, "showGrid+=0.3");
      }
      /**
       *
       */
      changeContent(position) {
         // Current imageCell
         const imageCell = this.imageCellArr[this.currentCell];
         // Upcoming imageCell
         const upcomingImageCell = this.imageCellArr[position];

         if (!imageCell || !upcomingImageCell) return;

         // Check if miniGrid cells exist
         if (this.DOM.miniGrid && this.DOM.miniGrid.cells) {
            // Remove current class from previous cell
            if (
               this.currentCell !== -1 &&
               this.DOM.miniGrid.cells[this.currentCell]
            ) {
               this.DOM.miniGrid.cells[this.currentCell].classList.remove(
                  "grid__cell--current"
               );
            }

            // Update current cell position
            this.currentCell = position;

            // Add current class to new cell
            if (this.DOM.miniGrid.cells[this.currentCell]) {
               this.DOM.miniGrid.cells[this.currentCell].classList.add(
                  "grid__cell--current"
               );
            }
         }

         // Calculate the transform to apply to the image cell
         const imageTransform = this.calcTransformImage();

         gsap
            .timeline({
               defaults: {
                  duration: 1,
                  ease: "expo.inOut",
               },
               onComplete: () => {
                  this.isAnimating = false;
               },
            })
            .addLabel("start", 0)
            .add(() => {
               if (imageCell.contentItem && imageCell.contentItem.textReveal) {
                  imageCell.contentItem.textReveal.out();
               }
               if (
                  imageCell.contentItem &&
                  imageCell.contentItem.textLinesReveal
               ) {
                  imageCell.contentItem.textLinesReveal.out();
               }
            }, "start")
            .add(() => {
               if (
                  imageCell.contentItem &&
                  imageCell.contentItem.DOM &&
                  imageCell.contentItem.DOM.el
               ) {
                  imageCell.contentItem.DOM.el.classList.remove(
                     "content__item--current"
                  );
               }
            })
            .set(
               [imageCell.DOM.el, upcomingImageCell.DOM.el],
               {
                  willChange: "transform, opacity",
               },
               "start"
            )
            .to(
               imageCell.DOM.el,
               {
                  opacity: 0,
                  scale: 0.8,
                  x: 0,
                  y: 0,
                  onComplete: () =>
                     gsap.set(imageCell.DOM.el, { willChange: "", zIndex: 1 }),
               },
               "start"
            )
            .to(
               imageCell.contentItem.DOM.nav.prev,
               {
                  y: "-100%",
               },
               "start"
            )
            .to(
               imageCell.contentItem.DOM.nav.next,
               {
                  y: "100%",
               },
               "start"
            )

            .addLabel("showContent", ">-=0.4")
            .set(
               upcomingImageCell.DOM.el,
               {
                  zIndex: 1001,
               },
               "start"
            )
            .to(
               upcomingImageCell.DOM.el,
               {
                  scale: imageTransform.scale,
                  x: imageTransform.x,
                  y: imageTransform.y,
                  opacity: 1,
                  onComplete: () =>
                     gsap.set(upcomingImageCell.DOM.el, { willChange: "" }),
               },
               "start"
            )
            .to(
               [
                  upcomingImageCell.contentItem.DOM.nav.prev,
                  upcomingImageCell.contentItem.DOM.nav.next,
               ],
               {
                  ease: "expo",
                  y: 0,
               },
               "showContent"
            )
            .add(() => {
               if (
                  upcomingImageCell.contentItem &&
                  upcomingImageCell.contentItem.textReveal
               ) {
                  upcomingImageCell.contentItem.textReveal.in();
               }
               if (
                  upcomingImageCell.contentItem &&
                  upcomingImageCell.contentItem.textLinesReveal
               ) {
                  upcomingImageCell.contentItem.textLinesReveal.in();
               }
            }, "showContent")
            .add(() => {
               if (
                  upcomingImageCell.contentItem &&
                  upcomingImageCell.contentItem.DOM &&
                  upcomingImageCell.contentItem.DOM.el
               ) {
                  upcomingImageCell.contentItem.DOM.el.classList.add(
                     "content__item--current"
                  );
               }
            }, "showContent+=0.02");
      }
      /**
       * Calculates the scale and translation values to apply to the image cell when we click on it.
       * Also used to recalculate those values on resize.
       * @return {JSON} the translation and scale values
       */
      calcTransformImage() {
         const cellrect = adjustedBoundingRect(
            this.imageCellArr[this.currentCell].DOM.el
         );
         return {
            scale: (winsize.width * 0.35) / cellrect.width,
            x: winsize.width * 0.65 - (cellrect.left + cellrect.width / 2),
            y: winsize.height * 0.5 - (cellrect.top + cellrect.height / 2),
         };
      }
   }

   // Initialize the Harmonic Gallery
   let eael_harmonic_gallery = document.querySelector(
      `#eael-hg-items-${dataSettings.widget_id}`
   );
   if (eael_harmonic_gallery) {
      eael_harmonic_gallery.eaelHarmonicGallery = new Harmonic_Gallery(
         eael_harmonic_gallery
      );

      // Update navigation items based on filter
      $scope.on("click", ".control", function () {
         var buttonFilter = $(this).attr("data-filter");
         if ($scope.find(".eael-hg-mini-wrapper").length > 0) {
            if (buttonFilter === "*") {
               $scope.find(".eael-hg-mini-wrapper .eael-hg-grid__cell").show();
               $scope.find(".eael-hg-content .eael-hg-content__item").show();
            } else {
               $scope.find(".eael-hg-mini-wrapper .eael-hg-grid__cell").hide();
               $scope
                  .find(
                     ".eael-hg-mini-wrapper .eael-hg-grid__cell" + buttonFilter
                  )
                  .show();

               $scope.find(".eael-hg-content .eael-hg-content__item").hide();
               $scope
                  .find(
                     ".eael-hg-content .eael-hg-content__item" + buttonFilter
                  )
                  .show();
            }
         }
      });
   }

   // Preload images then remove loader (loading class) from body
   preloadImages(".grid__cell-img-inner, .slide-nav__img").then(() =>
      document.body.classList.remove("loading")
   );
};

jQuery(window).on("elementor/frontend/init", function () {
   if (eael.elementStatusCheck("filterableGallery")) {
      return false;
   }

   elementorFrontend.hooks.addAction(
      "frontend/element_ready/eael-filterable-gallery.default",
      filterableGallery
   );

   // Listen for custom event when new items are loaded in Harmonic Layout
   jQuery(document).on(
      "eael:filterable-gallery:items-loaded",
      function (e, galleryId) {
         const gallery = document.querySelector(`#eael-hg-items-${galleryId}`);
         if (gallery && gallery.eaelHarmonicGallery) {
            gallery.eaelHarmonicGallery.initializeGallery();
         }
      }
   );

   // Listen for custom event when new items are loaded in Grid Flow Layout
   jQuery(document).on(
      "eael:filterable-gallery:items-loaded",
      function (e, galleryId) {
         const gallery = document.querySelector(`#eael-grid-fg-${galleryId}`);
         if (gallery && gallery.eaelGallery) {
            gallery.eaelGallery.initializeItems();
         }
      }
   );
});
