<template>
  <div class="add-books-page" style="max-width: 720px; margin: 2rem auto;">
    <h1 style="margin-bottom: 1rem;">Neues Buch hinzufügen</h1>

    <form @submit.prevent="submitForm" style="display: grid; gap: 1rem;">
      <div class="mb-3">
        <label for="isbn" class="form-label">ISBN</label>
        <input id="isbn" class="form-control"
               v-model.trim="form.isbn"
               type="text"
               required
               placeholder="z.B. 978-3-16-148410-0" />
        <small v-if="errors.isbn" style="color:#c00">{{ errors.isbn }}</small>
      </div>

      <div class="mb-3">
        <label for="title" class="form-label">Titel</label>
        <input id="title" class="form-control"
               v-model.trim="form.title"
               type="text"
               required
               placeholder="z.B. Der Prozess" />
        <small v-if="errors.title" style="color:#c00">{{ errors.title }}</small>
      </div>

      <div class="mb-3">
        <label for="author" class="form-label">Autor:in</label>
        <input id="author" class="form-control"
               v-model.trim="form.author"
               type="text"
               required
               placeholder="z.B. Franz Kafka" />
        <small v-if="errors.author" style="color:#c00">{{ errors.author }}</small>
      </div>

      <div class="mb-3">
        <label for="description" class="form-label">Beschreibung</label>
        <textarea id="description" class="form-control"
                  v-model.trim="form.description"
                  rows="4"
                  placeholder="Kurzbeschreibung..."></textarea>
        <small v-if="errors.description" style="color:#c00">{{ errors.description }}</small>
      </div>

      <div class="mb-3">
        <label for="status" class="form-label">Status</label>
        <select v-model="form.status" required>
            <option disabled value="">-- bitte auswählen --</option>
            <option value="available">Verfügbar</option>
            <option value="borrowed">Ausgeliehen</option>
            <option value="reserved">Reserviert</option>
            <option value="lost">Verloren</option>
        </select>
        <small v-if="errors.status" style="color:#c00">{{ errors.status }}</small>
    </div>


    <div class="mb-3">
        <label for="condition" class="form-label">Zustand</label>
        <select v-model="form.condition" required>
            <option value="new">Neu</option>
            <option value="used">Gebraucht</option>
            <option value="damaged">Beschädigt</option>
            <option value="missing_pages">Seiten fehlen</option>
            <option value="repaired">Repariert</option>
        </select>
        <small v-if="errors.condition" style="color:#c00">{{ errors.condition }}</small>
    </div>
      <div class="mb-3">
        <label for="category_per_age" class="form-label">Alterskategorie</label>
        <input id="category_per_age" class="form-control"
               v-model="form.category_per_age"
               type="text"
               placeholder="z.B. 3-6 Jahre" />
        <small v-if="errors.category_per_age" style="color:#c00">{{ errors.category_per_age }}</small>
      </div>


      <div style="display:flex; gap:.75rem; align-items:center;">
        <button class="btn btn-dark" type="submit" :disabled="loading">
          {{ loading ? 'Speichern...' : 'Buch anlegen' }}
        </button>
        <span v-if="successMessage" style="color:#0a0">{{ successMessage }}</span>
        <span v-if="serverError" style="color:#c00">{{ serverError }}</span>
      </div>
    </form>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  name: 'AddBooks',
  data() {
    return {
      form: {
        title: '',
        author: '',
        isbn: '',
        description: '',
        status: '', 
        condition: '',
        category_per_age: '',       
      },
      loading: false,
      errors: {},
      successMessage: '',
      serverError: ''
    }
  },
  mounted () {

  },
  methods: {
    async submitForm () {
        // Button unklickbar
        this.loading = true
        this.successMessage = ''
        this.serverError = ''
        this.errors = {}

        try {
            // Die Eingaben aus this.form werden gesammelt
            const formData = {
                isbn: this.form.isbn?.trim() || null,
                title: this.form.title?.trim() || null,
                author: this.form.author?.trim() || null,
                description: this.form.description?.trim() || null,
                status: this.form.status || null,
                condition: this.form.condition || null,
                category_per_age: this.form.category_per_age || null,
            }

            // Schickt die Daten als JSON an deine Laravel-Route /api/add-book
            const res = await axios.post('/api/add-book', formData)

                // Fehler-Meldung wird gelöscht. Success Nachricht wird gesetzt. Formular-Felder werden zurückgesetzt.
                this.serverError = ''                
                this.successMessage = res.data?.message || 'Buch erfolgreich angelegt.'
                this.form = {
                    isbn: '', 
                    title: '',
                    author: '', 
                    description: '',
                    status: '', 
                    condition: '', 
                    category_per_age: ''
                }
            } catch (err) {
                console.log('POST /api/add-book failed:', err?.response?.status, err?.response?.data)

                // 422 -> Validation Error
                if (err?.response?.status === 422 && err.response.data?.errors) {
                    const v = err.response.data.errors
                    const e = {}
                // Fehlermeldungen pro pro Inputfeld anzeigen
                for (const k in v) e[k] = Array.isArray(v[k]) ? v[k][0] : String(v[k])
                    this.errors = e
                } else {
                    this.serverError = err?.response?.data?.message || 'Es ist ein Fehler aufgetreten.'
                }
            } finally {
                // Button wieder klickbar.
                this.loading = false
            }
        }
    }
}
</script>
