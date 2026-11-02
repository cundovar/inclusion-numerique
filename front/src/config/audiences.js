/**
 * Les deux entrées publiques du site.
 * `key` correspond au champ `audience` d'une catégorie (parcours) dans le back-office.
 */
export const AUDIENCES = [
  {
    key: 'apprendre',
    path: '/apprendre',
    title: "J'apprends le numérique",
    lead: 'Pour les adultes et les seniors qui veulent se servir seuls d\'un ordinateur, d\'une tablette ou d\'un téléphone.',
    examples: ['Envoyer un courriel', 'Trouver le bon site officiel', 'Préparer une démarche en ligne'],
    icon: 'fas fa-laptop',
    iconBgClass: 'bg-blue-100',
    cardBgClass: 'surface-card--blue',
    otherAudienceText: 'Vous aidez quelqu\'un à utiliser le numérique ?',
  },
  {
    key: 'accompagner',
    path: '/accompagner',
    title: "J'accompagne des personnes",
    lead: 'Pour les médiateurs, les professionnels et les bénévoles qui aident d\'autres personnes à utiliser le numérique.',
    examples: ['Comprendre le besoin de la personne', 'Expliquer sans faire à sa place', 'Animer un atelier'],
    icon: 'fas fa-people-group',
    iconBgClass: 'bg-emerald-100',
    cardBgClass: 'surface-card--green',
    otherAudienceText: 'Vous voulez apprendre pour vous-même ?',
  },
]

export const getAudience = (key) => AUDIENCES.find((audience) => audience.key === key) ?? null

export const getOtherAudience = (key) => AUDIENCES.find((audience) => audience.key !== key) ?? null
