export default class PrimitiveMount {
  constructor(parent) {
    this.dev = !!(window && window.__THORM_DEV__);
    
    // create anchors
    const label = this.constructor.name.slice(0, -'Mount'.length).toLowerCase();
    if(this.dev){
      this.start = document.createComment(`${label}:start`);
      this.end   = document.createComment(`${label}:end`);
    } else {
      this.start = new Text('');
      this.end   = new Text('');
    }
    parent.append(this.start, this.end);
  }
}
