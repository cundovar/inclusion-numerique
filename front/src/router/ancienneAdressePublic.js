// Des navigateurs gardent en cache une ancienne redirection de /spa vers /public/spa/ : le routeur
// recevait alors /public/spa/ comme une adresse de cours et affichait une page vide (/spa/public/spa/).
// Une redirection côté serveur bouclerait avec ce cache : c'est donc le routeur qui remet la bonne adresse.
export const ancienneAdressePublic = {
  path: '/public/spa/:suite(.*)?',
  redirect: (to) => ({ path: `/${to.params.suite ?? ''}`, query: to.query, hash: to.hash })
}
