
document.ready = function (readyFn) {
  if (document.readyState == "loading") {
    // still loading, wait for the event
    document.addEventListener("DOMContentLoaded", readyFn);
  } else {
    // DOM is ready!
    readyFn();
  }
};
/*
 * Created by David Adams
 * https://codeshack.io/dynamic-select-images-html-javascript/
 *
 * Released under the MIT license
 */
class DynamicSelect {
  constructor(element, options = {}) {
    let defaults = {
      placeholder: "Select an option",
      columns: 1,
      name: "",
      width: "",
      height: "",
      data: [],
      onChange: function () {},
    };
    this.options = Object.assign(defaults, options);
    this.selectElement = typeof element === "string" ? document.querySelector(element) : element;
    for (const prop in this.selectElement.dataset) {
      if (this.options[prop] !== undefined) {
        this.options[prop] = this.selectElement.dataset[prop];
      }
    }
    this.name = this.selectElement.getAttribute("name") ? this.selectElement.getAttribute("name") : "dynamic-select-" + Math.floor(Math.random() * 1000000);
    console.log("Dynamic selector " + this.name + " intialized");
    if (!this.options.data.length) {
      let options = this.selectElement.querySelectorAll("option");
      for (let i = 0; i < options.length; i++) {
        this.options.data.push({
          value: options[i].value,
          text: options[i].innerHTML,
          img: options[i].getAttribute("data-img"),
          selected: options[i].selected,
          html: options[i].getAttribute("data-html"),
          imgWidth: options[i].getAttribute("data-img-width"),
          imgHeight: options[i].getAttribute("data-img-height"),
        });
      }
    }
    this.element = this._template();
    this.selectElement.replaceWith(this.element);
    this._updateSelected();
    this._eventHandlers();
  }

  _template() {
    let optionsHTML = "";
    for (let i = 0; i < this.data.length; i++) {
      let optionWidth = 100 / this.columns;
      let optionContent = "";
      if (this.data[i].html) {
        optionContent = this.data[i].html;
      } else {
        optionContent = `
                    ${this.data[i].img ? `<img src="${this.data[i].img}"  loading="lazy" alt="${this.data[i].text}" class="${this.data[i].imgWidth && this.data[i].imgHeight ? "dynamic-size" : ""}" style="${this.data[i].imgWidth ? "width:" + this.data[i].imgWidth + ";" : ""}${this.data[i].imgHeight ? "height:" + this.data[i].imgHeight + ";" : ""}">` : ""}
                    ${this.data[i].text ? '<span class="dynamic-select-option-text">' + this.data[i].text + "</span>" : ""}
                `;
      }
      optionsHTML += `
                <li class="dynamic-select-option${this.data[i].value == this.selectedValue ? " dynamic-select-selected" : ""}${this.data[i].text || this.data[i].html ? "" : " dynamic-select-no-text"}" data-value="${this.data[i].value}" style="width:${optionWidth}%;${this.height ? "height:" + this.height + ";" : ""}">
                    ${optionContent}
                </li>
            `;
    }
    let template = `
            <div data-name=${this.name} class="dynamic-select"${this.selectElement.id ? ' id="' + this.selectElement.id + '"' : ""} style="${this.width ? "width:" + this.width + ";" : ""}${this.height ? "height:" + this.height + ";" : ""}">
                <input type="hidden" name="${this.name}" value="${this.selectedValue}">
                <div class="dynamic-select-header" style="${this.width ? "width:" + this.width + ";" : ""}${this.height ? "height:" + this.height + ";" : ""}"><span class="dynamic-select-header-placeholder">${this.placeholder}</span></div>
                <ul class="dynamic-select-options" style="${this.options.dropdownWidth ? "width:" + this.options.dropdownWidth + ";" : ""}${this.options.dropdownHeight ? "height:" + this.options.dropdownHeight + ";" : ""}">${optionsHTML}</ul>
            </div>
        `;
    let element = document.createElement("div");
    element.innerHTML = template;
    return element;
  }

  _eventHandlers() {
    this.element.querySelectorAll(".dynamic-select-option").forEach((option) => {
      option.onclick = () => {
        this.element.querySelectorAll(".dynamic-select-selected").forEach((selected) => selected.classList.remove("dynamic-select-selected"));
        option.classList.add("dynamic-select-selected");
        this.element.querySelector(".dynamic-select-header").innerHTML = option.innerHTML;
        this.element.querySelector("input").value = option.getAttribute("data-value");
        this.data.forEach((data) => (data.selected = false));
        this.data.filter((data) => data.value == option.getAttribute("data-value"))[0].selected = true;
        this.element.querySelector(".dynamic-select-header").classList.remove("dynamic-select-header-active");
        this.options.onChange(option.getAttribute("data-value"), option.querySelector(".dynamic-select-option-text") ? option.querySelector(".dynamic-select-option-text").innerHTML : "", option);
      };
    });
    this.element.querySelector(".dynamic-select-header").onclick = () => {
      this.element.querySelector(".dynamic-select-header").classList.toggle("dynamic-select-header-active");
    };
    if (this.selectElement.id && document.querySelector('label[for="' + this.selectElement.id + '"]')) {
      document.querySelector('label[for="' + this.selectElement.id + '"]').onclick = () => {
        this.element.querySelector(".dynamic-select-header").classList.toggle("dynamic-select-header-active");
      };
    }
    document.addEventListener("click", (event) => {
      if (!event.target.closest('[data-name="' + this.name + '"]') && !event.target.closest('label[for="' + this.selectElement.id + '"]')) {
        this.element.querySelector(".dynamic-select-header").classList.remove("dynamic-select-header-active");
      }
    });
  }

  _updateSelected() {
    if (this.selectedValue) {
      this.element.querySelector(".dynamic-select-header").innerHTML = this.element.querySelector(".dynamic-select-selected").innerHTML;
    }
  }

  get selectedValue() {
    let selected = this.data.filter((option) => option.selected);
    selected = selected.length ? selected[0].value : "";
    return selected;
  }

  set data(value) {
    this.options.data = value;
  }

  get data() {
    return this.options.data;
  }

  set selectElement(value) {
    this.options.selectElement = value;
  }

  get selectElement() {
    return this.options.selectElement;
  }

  set element(value) {
    this.options.element = value;
  }

  get element() {
    return this.options.element;
  }

  set placeholder(value) {
    this.options.placeholder = value;
  }

  get placeholder() {
    return this.options.placeholder;
  }

  set columns(value) {
    this.options.columns = value;
  }

  get columns() {
    return this.options.columns;
  }

  set name(value) {
    this.options.name = value;
  }

  get name() {
    return this.options.name;
  }

  set width(value) {
    this.options.width = value;
  }

  get width() {
    return this.options.width;
  }

  set height(value) {
    this.options.height = value;
  }

  get height() {
    return this.options.height;
  }
}

let selector;

// Function to initialize DynamicSelect. [data-dynamic-select] is used a default selector.
function initializeDynamicSelects () {
   selector = typeof selector !== "undefined" ? selector : '[data-dynamic-select]:not([type="hidden"]):not([disabled])';
  const elements = document.querySelectorAll(selector);
  elements.forEach((select) => {
    // Check if it's already initialized to avoid re-initializing
    if (!select.dynamicSelectInitialized) {
      new DynamicSelect(select);
      select.dynamicSelectInitialized = true; // Mark as initialized
    }
  });
}

 document.ready(function () {
  initializeDynamicSelects();
 });


// Initialize any existing elements on page load

// Set up the MutationObserver
const observer = new MutationObserver((mutationsList) => {
  mutationsList.forEach((mutation) => {
    if (mutation.type === "childList") {
      mutation.addedNodes.forEach((node) => {
        if (node.nodeType === Node.ELEMENT_NODE) {
          // Check if the added node itself matches the selector
          if (node.matches(selector)) {
            console.log("new selector detected");
            initializeDynamicSelects([node]);
          }
          // Check if any child nodes of the added node match the selector
          const childElements = node.querySelectorAll(selector);
          if (childElements.length > 0) {
            console.log(childElements.length + " new selectors detected");
            initializeDynamicSelects(childElements);
          }
        }
      });
    }
  });
});

// Start observing the document for added nodes
 document.ready(function(){
  observer.observe(document.body, { childList: true, subtree: true });
  })
