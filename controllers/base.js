//control the basement,like the react
//base class is like the base.php
class BaseControl {
  constructor () {
    this.state = {
      html: ''
    }

    this.setProperty = this.setProperty.bind(this)
    this.mount = this.mount.bind(this)
    this.unmount = this.unmount.bind(this)
    this.refresh = this.refresh.bind(this)
  }

  // Internal function
  // whenever use this, wil update the property
  // 每个instent有两个存储器(property)，一个是外界给他的，一个是它本身的。元素表现形态是虚拟的dom
  setProperty (property = { anchor: '' }) {
    this.property = property
  }

//set new state, will rewrite the state
  setState (newState) {
    this.state = Object.assign({}, this.state, newState)
  }

//
  setHandler (selector, eventType, handler) {
    const { anchor } = this.property //anchor: the point where to get into the element; identification
    const mountingElement = elementUtils.getElement(anchor) // to put into where= the parent of the point document.queryselector
//find the element itself
    if (mountingElement && mountingElement.childNodes) {
      [...mountingElement.childNodes].map(childNode => {
        if (childNode.nodeType !== Node.TEXT_NODE) {
          const targetElement = elementUtils.getElement(selector, childNode)

          if (targetElement) targetElement.addEventListener(eventType, handler)
        }
      })
    }
  }
//
  getElement (selector) {
    const { anchor } = this.property
    const mountingElement = elementUtils.getElement(anchor)

    if (mountingElement && mountingElement.childNodes) {
      const [targetElement] = [...mountingElement.childNodes].map(childNode => {
        if (childNode.nodeType !== Node.TEXT_NODE) {
          return elementUtils.getElement(selector, childNode)
        } else {
          return null
        }
      }).filter(t => t)

      if (targetElement) return targetElement
    }

    return {}
  }

  // Cycle
  // render: get the result of all the html text
  // mount: put in where anchor
  // unmount: remove
  // make the element listenable: bind event listener
  mount () {
    const { anchor } = this.property
    const { html } = this.state //internal the storage
    const mountingElement = elementUtils.getElement(anchor)

    if (mountingElement) {
      elementUtils.setInnerHtml(html, mountingElement)
    }
  }

  unmount () {
    const { anchor } = this.property
    const mountingElement = elementUtils.getElement(anchor)
// remove element
    if (mountingElement && mountingElement.childNodes) [...mountingElement.childNodes].map(elementUtils.removeElement)
  }

  refresh () {
    this.unmount()// there are a line that if find then delete, if did not , then would not
    this.render()
    this.mount()
    this.bindHandlers()
  }//to delete a month and get into another month

  // Custom
  render () {} // Update state.html with setState
  bindHandlers () {} // Bind event listeners with property and setHandler
}