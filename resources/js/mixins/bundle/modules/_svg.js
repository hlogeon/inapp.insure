// require all svg
function importAll(r) {
  r.keys().forEach(r);
}

importAll(require.context('../../svg', false, /\.svg$/));
