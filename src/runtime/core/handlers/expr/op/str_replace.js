export function str_replace(a, b, c) {
  const search = String(a ?? '');
  const replace = String(b ?? '');
  const subject = String(c ?? '');
  return subject.split(search).join(replace);
}

