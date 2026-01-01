export class Scope {
  constructor(parent = null) {
    this.parent = parent;
    this.disposers = [];
  }

  onDispose(fn) {
    this.disposers.push(fn);
  }
  
  dispose() {
    for (let i=this.disposers.length-1;i>=0;i--) {
      try{ this.disposers[i]();
      } catch(e) {
        console.warn(e); 
      } 
    }
    this.disposers.length = 0;
  }

  fork() {
    return new Scope(this);
  }
}
