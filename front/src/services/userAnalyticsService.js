/**
 * Service de gestion des cookies et analytics utilisateur
 * Respecte le RGPD et permet de tracker le comportement des utilisateurs
 * 
 * @author Mon App
 * @version 1.0
 */

class UserAnalyticsService {
    constructor() {
        // Configuration des cookies
        this.config = {
            cookiePrefix: 'myapp_', // Préfixe pour tous nos cookies
            sessionDuration: 30 * 60 * 1000, // 30 minutes en millisecondes
            consentCookieName: 'analytics_consent', // Cookie de consentement
            sessionCookieName: 'session_id', // Cookie de session
            userDataCookieName: 'user_data' // Cookie des données utilisateur
        };

        // État du consentement utilisateur
        this.hasConsent = false;
        
        // ID de session unique pour cet utilisateur
        this.sessionId = null;
        
        // Données de session en cours
        this.sessionData = {
            startTime: Date.now(),
            pageViews: [],
            actions: [],
            timeOnPages: {},
            userAgent: navigator.userAgent,
            screenResolution: `${screen.width}x${screen.height}`
        };

        // Initialisation du service
        this.init();
    }

    /**
     * Initialise le service analytics
     * Vérifie le consentement et démarre le tracking si autorisé
     */
    init() {
        console.log('🔍 Initialisation du service analytics...');
        
        // Vérifie si l'utilisateur a déjà donné son consentement
        this.hasConsent = this.getCookie(this.config.consentCookieName) === 'true';
        
        if (this.hasConsent) {
            this.startTracking();
        } else {
            // Affiche le banner de consentement
            this.showConsentBanner();
        }
    }

    /**
     * Démarre le tracking après consentement
     */
    startTracking() {
        console.log('✅ Consentement accordé - Démarrage du tracking');
        
        // Génère ou récupère l'ID de session
        this.sessionId = this.getOrCreateSessionId();
        
        // Track la page actuelle
        this.trackPageView(window.location.pathname);
        
        // Met en place les listeners d'événements
        this.setupEventListeners();
        
        // Sauvegarde périodique des données
        this.startPeriodicSave();
    }

    /**
     * Génère ou récupère un ID de session unique
     * @returns {string} ID de session
     */
    getOrCreateSessionId() {
        let sessionId = this.getCookie(this.config.sessionCookieName);
        
        if (!sessionId) {
            // Génère un nouvel ID de session (UUID simple)
            sessionId = 'sess_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
            
            // Sauvegarde dans un cookie de session (expire à la fermeture du navigateur)
            this.setCookie(this.config.sessionCookieName, sessionId, null);
            
            console.log('🆕 Nouvel ID de session créé:', sessionId);
        } else {
            console.log('🔄 Session existante récupérée:', sessionId);
        }
        
        return sessionId;
    }

    /**
     * Track une vue de page
     * @param {string} pagePath - Chemin de la page
     * @param {string} pageTitle - Titre de la page (optionnel)
     */
    trackPageView(pagePath, pageTitle = document.title) {
        if (!this.hasConsent) return;
        
        const pageView = {
            path: pagePath,
            title: pageTitle,
            timestamp: Date.now(),
            referrer: document.referrer,
            timeSpent: 0 // Sera calculé quand l'utilisateur quitte la page
        };
        
        // Si ce n'est pas la première page, calcule le temps passé sur la précédente
        if (this.sessionData.pageViews.length > 0) {
            const previousPage = this.sessionData.pageViews[this.sessionData.pageViews.length - 1];
            previousPage.timeSpent = Date.now() - previousPage.timestamp;
        }
        
        this.sessionData.pageViews.push(pageView);
        
        console.log('📄 Page vue trackée:', pagePath);
        
        // Sauvegarde immédiate
        this.saveSessionData();
    }

    /**
     * Track une action utilisateur spécifique
     * @param {string} action - Type d'action (click, scroll, form_submit, etc.)
     * @param {string} element - Élément concerné (bouton, lien, etc.)
     * @param {object} data - Données supplémentaires (optionnel)
     */
    trackAction(action, element, data = {}) {
        if (!this.hasConsent) return;
        
        const actionData = {
            type: action,
            element: element,
            timestamp: Date.now(),
            page: window.location.pathname,
            data: data
        };
        
        this.sessionData.actions.push(actionData);
        
        console.log('🎯 Action trackée:', action, 'sur', element);
        
        // Sauvegarde si c'est une action importante
        if (['form_submit', 'download', 'qcm_complete'].includes(action)) {
            this.saveSessionData();
        }
    }

    /**
     * Track spécifiquement les QCM (Questions à Choix Multiples)
     * @param {string} qcmId - ID du QCM
     * @param {string} action - Action (start, answer, complete, abandon)
     * @param {object} details - Détails supplémentaires
     */
    trackQCM(qcmId, action, details = {}) {
        this.trackAction('qcm_' + action, `qcm_${qcmId}`, {
            qcmId: qcmId,
            score: details.score || null,
            timeSpent: details.timeSpent || null,
            category: details.category || null
        });
    }

    /**
     * Track le temps passé sur la page courante
     */
    trackTimeOnPage() {
        if (!this.hasConsent) return;
        
        const currentPage = window.location.pathname;
        const now = Date.now();
        
        if (!this.sessionData.timeOnPages[currentPage]) {
            this.sessionData.timeOnPages[currentPage] = {
                totalTime: 0,
                visits: 0,
                lastEnter: now
            };
        }
        
        this.sessionData.timeOnPages[currentPage].lastEnter = now;
        this.sessionData.timeOnPages[currentPage].visits++;
    }

    /**
     * Met en place les listeners d'événements automatiques
     */
    setupEventListeners() {
        // Track les clics sur les liens importants
        document.addEventListener('click', (event) => {
            const target = event.target;
            
            // Track clics sur les boutons
            if (target.tagName === 'BUTTON') {
                this.trackAction('button_click', target.textContent || target.className);
            }
            
            // Track clics sur les liens externes
            if (target.tagName === 'A' && target.href && !target.href.includes(window.location.origin)) {
                this.trackAction('external_link_click', target.href);
            }
        });
        
        // Track le scroll (une fois par page)
        let scrollTracked = false;
        window.addEventListener('scroll', () => {
            if (!scrollTracked && window.scrollY > 100) {
                this.trackAction('scroll', 'page_scroll');
                scrollTracked = true;
            }
        });
        
        // Track la fermeture/rafraîchissement de page
        window.addEventListener('beforeunload', () => {
            this.calculateFinalTimeOnPage();
            this.saveSessionData();
        });
        
        // Track les changements de visibilité (onglet caché/visible)
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                this.trackAction('page_hidden', window.location.pathname);
            } else {
                this.trackAction('page_visible', window.location.pathname);
            }
        });
    }

    /**
     * Calcule le temps final passé sur la page courante
     */
    calculateFinalTimeOnPage() {
        const currentPage = window.location.pathname;
        const pageTimeData = this.sessionData.timeOnPages[currentPage];
        
        if (pageTimeData && pageTimeData.lastEnter) {
            const timeSpent = Date.now() - pageTimeData.lastEnter;
            pageTimeData.totalTime += timeSpent;
        }
        
        // Met à jour aussi la dernière page vue
        if (this.sessionData.pageViews.length > 0) {
            const lastPageView = this.sessionData.pageViews[this.sessionData.pageViews.length - 1];
            if (lastPageView.timeSpent === 0) {
                lastPageView.timeSpent = Date.now() - lastPageView.timestamp;
            }
        }
    }

    /**
     * Sauvegarde périodique des données de session
     */
    startPeriodicSave() {
        setInterval(() => {
            this.saveSessionData();
        }, 60000); // Sauvegarde toutes les minutes
    }

    /**
     * Sauvegarde les données de session dans les cookies et envoie au serveur
     */
    saveSessionData() {
        if (!this.hasConsent) return;
        
        // Met à jour le temps sur la page courante
        this.calculateFinalTimeOnPage();
        
        // Prépare les données à sauvegarder
        const dataToSave = {
            sessionId: this.sessionId,
            ...this.sessionData,
            lastUpdate: Date.now()
        };
        
        // Sauvegarde en cookie (données limitées pour éviter de dépasser la taille max)
        const lightData = {
            sessionId: this.sessionId,
            pageCount: this.sessionData.pageViews.length,
            actionCount: this.sessionData.actions.length,
            lastUpdate: Date.now()
        };
        
        this.setCookie(this.config.userDataCookieName, JSON.stringify(lightData), 1); // 1 jour
        
        // Envoie au serveur (si endpoint disponible)
        this.sendToServer(dataToSave);
        
        console.log('💾 Données de session sauvegardées');
    }

    /**
     * Envoie les données analytics au serveur
     * @param {object} data - Données à envoyer
     */
    async sendToServer(data) {
        try {
            // Endpoint à créer dans ton backend Symfony
            const response = await fetch('/api/analytics/track', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(data)
            });
            
            if (response.ok) {
                console.log('📤 Données envoyées au serveur avec succès');
            } else {
                console.warn('⚠️ Erreur lors de l\'envoi des données:', response.status);
            }
        } catch (error) {
            console.error('❌ Erreur réseau lors de l\'envoi:', error);
            // On continue quand même, les données restent en local
        }
    }

    /**
     * Affiche le banner de consentement RGPD
     */
    showConsentBanner() {
        // Vérifie qu'il n'y a pas déjà un banner
        if (document.getElementById('consent-banner')) return;
        
        const banner = document.createElement('div');
        banner.id = 'consent-banner';
        banner.innerHTML = `
            <div style="
                position: fixed;
                bottom: 0;
                left: 0;
                right: 0;
                background: #2d3748;
                color: white;
                padding: 20px;
                text-align: center;
                z-index: 9999;
                box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
            ">
                <div style="max-width: 1200px; margin: 0 auto;">
                    <p style="margin: 0 0 15px 0;">
                        🍪 Ce site utilise des cookies pour analyser votre navigation et améliorer votre expérience.
                        <a href="#" style="color: #63b3ed;">En savoir plus</a>
                    </p>
                    <button id="accept-cookies" style="
                        background: #48bb78;
                        color: white;
                        border: none;
                        padding: 10px 20px;
                        margin: 0 10px;
                        border-radius: 5px;
                        cursor: pointer;
                    ">Accepter</button>
                    <button id="refuse-cookies" style="
                        background: #e53e3e;
                        color: white;
                        border: none;
                        padding: 10px 20px;
                        margin: 0 10px;
                        border-radius: 5px;
                        cursor: pointer;
                    ">Refuser</button>
                </div>
            </div>
        `;
        
        document.body.appendChild(banner);
        
        // Gestion des clics
        document.getElementById('accept-cookies').addEventListener('click', () => {
            this.grantConsent();
            banner.remove();
        });
        
        document.getElementById('refuse-cookies').addEventListener('click', () => {
            this.revokeConsent();
            banner.remove();
        });
    }

    /**
     * Accorde le consentement et démarre le tracking
     */
    grantConsent() {
        this.hasConsent = true;
        this.setCookie(this.config.consentCookieName, 'true', 365); // 1 an
        console.log('✅ Consentement accordé par l\'utilisateur');
        this.startTracking();
    }

    /**
     * Révoque le consentement et supprime les données
     */
    revokeConsent() {
        this.hasConsent = false;
        this.setCookie(this.config.consentCookieName, 'false', 365); // 1 an
        
        // Supprime tous les cookies analytics
        this.deleteCookie(this.config.sessionCookieName);
        this.deleteCookie(this.config.userDataCookieName);
        
        console.log('❌ Consentement révoqué par l\'utilisateur');
    }

    /**
     * Utilitaire pour créer un cookie
     * @param {string} name - Nom du cookie
     * @param {string} value - Valeur du cookie
     * @param {number} days - Nombre de jours avant expiration (null = session)
     */
    setCookie(name, value, days) {
        const fullName = this.config.cookiePrefix + name;
        let expires = '';
        
        if (days) {
            const date = new Date();
            date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
            expires = '; expires=' + date.toUTCString();
        }
        
        document.cookie = fullName + '=' + encodeURIComponent(value) + expires + '; path=/; SameSite=Lax';
    }

    /**
     * Utilitaire pour lire un cookie
     * @param {string} name - Nom du cookie
     * @returns {string|null} Valeur du cookie ou null
     */
    getCookie(name) {
        const fullName = this.config.cookiePrefix + name + '=';
        const cookies = document.cookie.split(';');
        
        for (let cookie of cookies) {
            cookie = cookie.trim();
            if (cookie.indexOf(fullName) === 0) {
                return decodeURIComponent(cookie.substring(fullName.length));
            }
        }
        return null;
    }

    /**
     * Utilitaire pour supprimer un cookie
     * @param {string} name - Nom du cookie
     */
    deleteCookie(name) {
        this.setCookie(name, '', -1);
    }

    /**
     * Récupère les statistiques de session courante
     * @returns {object} Statistiques
     */
    getSessionStats() {
        return {
            sessionId: this.sessionId,
            sessionDuration: Date.now() - this.sessionData.startTime,
            pageViews: this.sessionData.pageViews.length,
            actions: this.sessionData.actions.length,
            currentPage: window.location.pathname,
            hasConsent: this.hasConsent
        };
    }

    /**
     * Méthode de debug pour afficher les données collectées
     */
    debug() {
        console.log('🔍 Debug Analytics Service:');
        console.log('Consentement:', this.hasConsent);
        console.log('Session ID:', this.sessionId);
        console.log('Données de session:', this.sessionData);
        console.log('Statistiques:', this.getSessionStats());
    }
}

// Export pour utilisation dans d'autres fichiers
export default UserAnalyticsService;

// Si tu veux l'utiliser directement dans le HTML (sans modules ES6)
if (typeof window !== 'undefined') {
    window.UserAnalyticsService = UserAnalyticsService;
}