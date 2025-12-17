<template>
  <div
    class="modal fade"
    :id="id"
    tabindex="-1"
    :aria-labelledby="`${id}-label`"
    aria-hidden="true"
    ref="modalEl"
  >
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h1 class="modal-title" :id="`${id}-label`">Über die Kita-Bücherei</h1>

          <button
            type="button"
            class="btn-close"
            data-bs-dismiss="modal"
            aria-label="Schließen"
          />
        </div>

        <div class="modal-body">
          <div class="container-fluid">
            <!-- Beschreibung -->
            <section class="mb-4">
              <h4 class="mb-2">Was macht diese Anwendung?</h4>
              <p class="mb-2">
                Diese Anwendung hilft dabei, eine kleine Bibliothek – z. B. in einer Kita, Schule oder einem
                Verein – übersichtlich zu verwalten: Bücher können erfasst, gesucht und organisiert werden,
                und der aktuelle Status (verfügbar, ausgeliehen, beschädigt usw.) bleibt jederzeit
                nachvollziehbar.
              </p>
              <p class="mb-0">
                In diesem Fall handelt es sich konkret um eine Kita-Bücherei: Das Kita-Personal pflegt und
                verwaltet den Bestand, während die Kinder eine bebilderte Liste der aktuell verfügbaren Bücher
                sehen. Über ein eigenes Foto (per Touch/Klick) können sich die Kinder in ihr persönliches Konto
                einloggen. Direkt danach erscheint automatisch die bebilderte Liste mit den verfügbaren Büchern.
                Tippt ein Kind auf das Cover eines Buches, wird das Buch automatisch ausgeliehen. Bereits ausgeliehene
                oder beschädigte Bücher werden den Kindern nicht angezeigt.
              </p>
            </section>

            <!-- Features -->
            <section class="mb-4">
              <h4 class="mb-2">Hauptfeatures</h4>
              <ul class="mb-0">
                <li>Bücher anlegen und verwalten (Titel, Autor, Beschreibung, Kategorien, Cover, …)</li>
                <li>Bücher mit Hilfe von KI anlegen: nur ISBN eingeben und <strong>mit KI autoausfüllen</strong></li>
                <li>Suchen, Filtern und schnelles Finden von Einträgen</li>
                <li>Status/Verfügbarkeit im Blick behalten (z. B. verfügbar, ausgeliehen, reserviert, verloren)</li>
                <li>Benutzer-Login</li>
                <li>Übersichtliche Oberfläche für den Alltag</li>
              </ul>
            </section>

            <!-- Entwicklungsphase -->
            <section class="mb-4">
              <h4 class="mb-2">Hinweis zur Entwicklungsphase (MVP)</h4>
              <p class="mb-2">
                Diese Anwendung befindet sich aktuell noch in der Entwicklungsphase. Nicht alle Funktionen sind bereits
                vollständig umgesetzt, und auch das Design/Layout wird noch weiter verbessert.
              </p>
              <p class="mb-0">
                Die Entwicklung folgt dem Prinzip MVP (Minimum Viable Product): Zuerst werden die wichtigsten
                Kernfunktionen umgesetzt, damit Nutzerinnen und Nutzer einen klaren Überblick bekommen, worum es in der
                Anwendung geht und wie sie grundsätzlich funktioniert.
              </p>
            </section>

            <!-- Testen -->
            <section class="mb-0">
              <h4 class="mb-2">Wie kann ich die Anwendung testen?</h4>
              <ol class="mb-3">
                <li>Du kannst dich mit <strong>Fake-Daten registrieren</strong> (Fantasie-Name/E-Mail).</li>
                <li>Oder nutze den Demo-Account unten zum schnellen Einloggen.</li>
                <li>Teste dann typische Abläufe: Bücher suchen, Details ansehen, Status prüfen, etc.</li>
              </ol>

              <div class="alert alert-info mb-0">
                <h5 class="mb-2">Demo-Login</h5>

                <div><strong>E-Mail:</strong> <code>{{ demoEmail }}</code></div>
                <div><strong>Passwort:</strong> <code>{{ demoPassword }}</code></div>

                <div class="mt-2 small">
                  Hinweis: Bitte keine echten persönlichen Daten verwenden – nutze für Tests gern Fantasie-Daten.
                </div>
              </div>
            </section>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            Schließen
          </button>

          <button type="button" class="btn btn-primary" @click="close">
            Los geht’s
          </button>
        </div>
      </div>
    </div>
    
  </div>
 
</template>

<script>
export default {
  name: "IntroModal",

  props: {
    id: { type: String, default: "introModal" },
    autoShow: { type: Boolean, default: false },
  },

  data() {
    return {
      modalInstance: null,
      demoEmail: "test@test.de",
      demoPassword: "test1234",
    };
  },

  mounted() {
    if (!window.bootstrap || !this.$refs.modalEl) return;

    this.modalInstance = window.bootstrap.Modal.getOrCreateInstance(this.$refs.modalEl);

    if (this.autoShow) this.modalInstance.show();
},

  methods: {
    close() {
      this.modalInstance?.hide();
    },
  },
};
</script>

<style lang="scss" src="../../css/intro-modal.scss"></style>

