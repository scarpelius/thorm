export function createCursor(root) {
  const indices = new WeakMap();

  function nextNode(parent) {
    const i = indices.get(parent) || 0;
    const nodes = parent.childNodes;
    if (i >= nodes.length) return null;
    const node = nodes[i];
    indices.set(parent, i + 1);
    return node;
  }

  function assertNode(node, msg) {
    if (!node) {
      throw new Error(msg);
    }
  }

  function nextElement(parent, tag) {
    const node = nextNode(parent);
    assertNode(node, `[hydrate] expected <${tag}> but no node found`);
    if (node.nodeType !== 1) {
      throw new Error(`[hydrate] expected <${tag}> but got nodeType ${node.nodeType}`);
    }
    if (tag && node.tagName.toLowerCase() !== String(tag).toLowerCase()) {
      throw new Error(`[hydrate] expected <${tag}> but got <${node.tagName.toLowerCase()}>`);
    }
    return node;
  }

  function nextText(parent) {
    const node = nextNode(parent);
    assertNode(node, '[hydrate] expected text node but no node found');
    if (node.nodeType !== 3) {
      throw new Error(`[hydrate] expected text node but got nodeType ${node.nodeType}`);
    }
    return node;
  }

  function nextComment(parent, label) {
    const node = nextNode(parent);
    assertNode(node, `[hydrate] expected comment ${label} but no node found`);
    if (node.nodeType !== 8) {
      throw new Error(`[hydrate] expected comment ${label} but got nodeType ${node.nodeType}`);
    }
    if (label && node.nodeValue !== label) {
      throw new Error(`[hydrate] expected comment ${label} but got ${node.nodeValue}`);
    }
    return node;
  }

  return { nextNode, nextElement, nextText, nextComment };
}

