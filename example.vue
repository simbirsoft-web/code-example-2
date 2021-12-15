<template>
  <v-container fluid class="main pa-0 ma-0 d-flex fill-height">
    <div class="breadcrumbs">
      <occurrenceNav
        :occurrence="occurrence"
        :occurrence-link="`/occurrence/${occurrence ? occurrence.id : null}`"
        suffix="article"
        :text-none="true"
      />
    </div>
    <div class="fill-height order-body">
      <v-row class="d-flex justify-space-around article-title align-center">
        <v-col
          cols="6"
          class="legend-left"
        >
          <button
            class="article-send-button"
            :class="{'disabled': isarticleViewAvailable}"
            @click="addstockDialogIsOpen=true"
            :disabled="isarticleViewAvailable"
          >
            <v-icon>{{ "$plus" }}</v-icon>
            <span>Add</span>
          </button>
          <v-divider
            class="divider divider-left"
            vertical
          />
        </v-col>
        <v-col
          cols="3"
          class="legend-center"
        >
        <span class="order-sent-text">
          Last Send:
          <span class="order-sent-time">
            {{ timeSinceLastSend }}
          </span>
        </span>
        </v-col>
        <v-col
          cols="3"
          class="legend-right"
        >
        <v-divider
          class="divider divider-right"
          vertical
        />
        <CutCornerBlock
          @click="sendarticle()"
          :disabled="isarticleViewAvailable"
          id="occurrence-order-btn-add-stock"
          type="button"
          icon="$send"
          text="Отправить отчет"
          text-color="#000000"
          background="#00FFC2"
          border="#00FFC2"
          class="align-self-center"
        />
        </v-col>
      </v-row>
      <stockList
        v-if="actorstocks.length > 0"
        class="stock-list scrollable"
        @changestock="changestock($occurrence)"
        @deletestock="deletestockDialog($occurrence)"
        @changeProtection="changeProtection($occurrence)"
        @changeRestoration="changeRestoration($occurrence)"
        @changeComment="changeComment($occurrence)"
        :actionTypes="actionTypes"
        :stockList="stockList"
        :stocks="actorstocks"
        :disableFields="isarticleViewAvailable"
      />
    </div>
    <v-dialog
      :value="addstockDialogIsOpen"
      @click:outside="addstockDialogIsOpen = false"
      width="800px"
    >
      <DefaultPopupForm
        id="users-upload-form"
        title-id="users-upload-form-title"
        class="pa-0"
        :close-button="true"
        @close="closeAddstockDialog()"
      >
        <template v-slot:title>New</template>
        <template v-slot:content>
          <v-container class="dialog-popup">
            <span class="add-action-popup-title">type</span>
            <v-select
              v-model="actoractionType"
              :items="actionTypes"
              item-text="title"
              :menu-props="{
                contentClass: 'stock-create-select',
                dark: true
              }"
              item-value="id"
              @input="setactoractionType($occurrence)"
              placeholder="Choose type"
              no-data-text="no data"
            />
            <v-select
              v-model="actorstock"
              :items="stockList"
              item-text="title"
              :menu-props="{
                contentClass: 'stock-create-select',
                dark: true
              }"
              item-value="id"
              @input="setactorstock($occurrence)"
              placeholder="Choose type"
              no-data-text="no data"
              :disabled="!actoractionType"
            />
          </v-container>
          <v-card-actions slot="footer">
            <v-spacer/>
            <v-btn
              id="add-stock-ok-button"
              color="cyan_aqua"
              tile
              dark
              class="font-weight-bold form-ok-button"
              text
              @click="addstock()"
              :disabled="noSelectedstock"
            >
              Create
            </v-btn>
            <v-spacer/>
          </v-card-actions>
        </template>
      </DefaultPopupForm>
    </v-dialog>
    <v-dialog
      :value="deletestockDialogIsOpen"
      @click:outside="deletestockDialogIsOpen = false"
      width="610px"
    >
      <DefaultPopupForm
        class="delete-entity-form"
        id="delete-stock-form"
        title-id="delete-stock-form-title"
      >
        <template v-slot:title>Delete/template>
        <template v-slot:content>
          <v-container class="d-flex flex-column delete-entity-container">
            <div class="delete-stock-name pt-6 align-self-center">
              {{ deletedstock ? deletedstock.tactic : '' }}
              <v-icon class="mx-1" small>$breadcrumb_triangle</v-icon>
              {{ deletedstock ? deletedstock.title : '' }}
            </div>
            <div class="delete-entity-description pt-7">
              Do you want to delete article?
            </div>
          </v-container>
          <v-card-actions slot="footer">
            <v-spacer/>
            <v-btn
              id="delete-stock-ok-button"
              tile
              dark
              class="font-weight-bold form-ok-button"
              text
              @click="deletestock()"
            >
              Delete
            </v-btn>
            <v-btn
              id="delete-stock-cancel-button"
              color="cyan_aqua"
              tile
              dark
              class="font-weight-bold form-cancel-button"
              text
              @click="deletestockDialogIsOpen = false"
            >
              Cancel
            </v-btn>
            <v-spacer/>
          </v-card-actions>
        </template>
      </DefaultPopupForm>
    </v-dialog>
    <v-dialog
      :value="articleForViewOnlyDialogIsOpen"
      @click:outside="articleForViewOnlyDialogIsOpen = false"
      width="800px"
    >
      <DefaultPopupForm
        id="article-view-form"
        title-id="article-view-form-title"
        class="pa-0"
        :close-button="true"
        @close="articleForViewOnlyDialogIsOpen = false"
      >
        <template v-slot:content>
          <v-container class="d-flex flex-column article-view-container">
            <div class="article-view-name pt-6 align-self-center">
              No data
            </div>
          </v-container>
          <v-card-actions slot="footer">
            <v-spacer/>
            <v-btn
              id="article-view-form-ok-button"
              color="cyan_aqua"
              tile
              dark
              class="font-weight-bold popup-form-ok-button"
              text
              @click="articleForViewOnlyDialogIsOpen = false"
            >
              OK
            </v-btn>
            <v-spacer/>
          </v-card-actions>
        </template>
      </DefaultPopupForm>
    </v-dialog>
    <v-dialog
      :value="showErrorDialog"
      width="800px"
    >
      <DefaultPopupForm
        id="article-view-form"
        title-id="article-view-form-title"
        class="pa-0"
        :close-button="true"
        @close="showErrorDialog = false"
      >
        <template v-slot:content>
          <v-container class="d-flex flex-column article-view-container">
            <div class="article-view-name pt-6 align-self-center">
              Errors found
            </div>
          </v-container>
          <v-card-actions slot="footer">
            <v-spacer/>
            <v-btn
              @click="showErrorDialog = false"
              id="article-view-form-ok-button"
              class="font-weight-bold popup-form-ok-button"
              color="cyan_aqua"
              tile
              dark
              text
            >
              ОК
            </v-btn>
            <v-spacer/>
          </v-card-actions>
        </template>
      </DefaultPopupForm>
    </v-dialog>
  </v-container>
</template>

<script>

import { mapGetters } from 'vuex'
import moment from 'moment'
import occurrences from '@/helpers/occurrences.helper'
import actionService from '@/services/action.service'
import scoringService from '@/services/scoring.service'
import ForbiddenBackoccurrences from '@/components/error/ForbiddenBackoccurrences'
import _ from 'lodash'

export default {
  name: 'occurrenceactorarticle',
  props: {
    occurrenceId: String
  },
  data () {
    return {
      occurrence: null,
      actoractionType: null,
      actorstock: null,
      showErrorDialog: false,
      actionTypes: [],
      actorarticle: {
        article: {
          stocks: []
        }
      },
      addstockDialogIsOpen: false,
      deletestockDialogIsOpen: false,
      articleForViewOnlyDialogIsOpen: false,
      timeSinceLastSend: '00:00',
      deletedstock: '',
      isarticleChanged: false,
      timeCorrection: null
    }
  },
  computed: {
    ...mapGetters('account', ['currentUserId']),
    articleLastSentTime () {
      return (this.actorarticle?.article && this.actorarticle?.article.submitted_at) || ''
    },
    stockList () {
      return this.actoractionType?.stocks || []
    },
    actorstocks () {
      return this.actorarticle?.article?.stocks || []
    },
    completedstocks () {
      if (this.actorarticle?.article?.stocks && (this.actorarticle.article.stocks.length > 0)) {
        return true
      }
      return false
    },
    isarticleEditAvailable () {
      return this.occurrence && ([occurrences.STARTED.key, occurrences.PAUSE.key, occurrences.PREPARATION_PAUSE.key].indexOf(this.occurrence.occurrenceStatus) !== -1)
    },
    isarticleViewAvailable () {
      return this.occurrence && ([occurrences.FINISHED.key, occurrences.SUMMING_UP_RESULTS.key].indexOf(this.occurrence.occurrenceStatus) !== -1)
    },
    noSelectedstock () {
      return !(this.actoractionType && this.actorstock)
    }
  },
  watch: {
    isarticleViewAvailable () {
      this.articleForViewOnlyDialogIsOpen = this.isarticleViewAvailable
    }
  },
  created () {
    this.debouncedSaveDraft = _.debounce(this.saveDraft, 1000)
  },
  async mounted () {
    await Promise.all([this.getoccurrence(), this.getarticle()])
    this.articleForViewOnlyDialogIsOpen = this.isarticleViewAvailable
    this.getoccurrenceInterval = setInterval(async () => {
      this.getoccurrence()
    }, 10000)
    this.loadTactics()
    this.interval = window.setInterval(() => {
      if (this.articleLastSentTime) {
        const diffInSeconds = moment().diff(this.articleLastSentTime) - (this.timeCorrection || 0)
        this.timeSinceLastSend = moment(diffInSeconds).utc().format('mm:ss')
      }
    }, 1000)
  },
  methods: {
    async loadTactics () {
      const tactics = await scoringService.getTactics(this.occurrenceId)
      if (tactics) {
        this.actionTypes = tactics
      }
    },
    getoccurrence () {
      return new Promise((resolve, reject) => {
        occurrenceService.getoccurrenceWithScoringCheck(this.occurrenceId)
          .then(data => {
            this.occurrence = data
            if (!(this.isarticleEditAvailable || this.isarticleViewAvailable)) {
              this.$router.push('/occurrence/' + this.occurrence.id)
            }
            resolve()
          })
          .catch((error) => {
            if (error.response.status === 400) {
              this.$router.push({
                name: 'error',
                params: {
                  errorText: 'No articles available',
                  action: ForbiddenBackoccurrences
                }
              })
            } else {
              reject(error)
            }
          })
      })
    },
    getarticle () {
      return new Promise(resolve => {
        scoringService.getUserarticleV2(this.occurrenceId, this.currentUserId)
          .then(actorarticle => {
            this.actorarticle = actorarticle
            this.setarticleIds()
          })
          .catch(() => {
            console.error('Error at loading default user article')
          })
          .finally(() => {
            this.actorarticle.revision = 1
          })
        resolve()
      })
    },
    setactoractionType (typeId) {
      this.actoractionType = this.actionTypes.find(type => type.id === typeId) || {}
      this.actorstock = null
    },
    setactorstock (actionId) {
      this.actorstock = this.actoractionType.stocks?.find(action => action.id === actionId) || {}
    },
    generatestockId () {
      const stocks = this.actorarticle?.article?.stocks || []
      const maxId = stocks.reduce((accumulator, currentstock) => {
        if ((+currentstock.id || 0) > accumulator) {
          accumulator = currentstock.id
        }
        return accumulator
      }, 0)
      return maxId + 1
    },
    closeAddstockDialog () {
      this.addstockDialogIsOpen = false
      this.actoractionType = null
      this.actorstock = null
    },
    addstock () {
      this.addstockDialogIsOpen = false
      const attributesData = [...this.actorstock.attributes]
      const attributes = []
      attributesData.forEach(attribute => {
        attributes.push(_.cloneDeep(_.pick(attribute, ['title'])))
      })
      const stock = {
        id: this.generatestockId(),
        tactic: this.actoractionType.title,
        title: this.actorstock.title,
        code: this.actorstock.code,
        protection: {
          value: ''
        },
        restoration: {
          value: ''
        },
        comment: '',
        attributes: attributes
      }
      this.actorarticle.article.stocks.unshift(stock)
      this.actoractionType = null
      this.actorstock = null
      this.debouncedSaveDraft()
    },
    changestock (data) {
      const stock = this.actorarticle.article.stocks.find(stock => stock.id === data.stockId)
      const attribute = stock.attributes.find(attribute => attribute.title === data.attributeTitle)
      if (attribute) {
        attribute.value = data.value
      } else {
        stock.attributes.push({
          value: data.value
        })
      }
      this.debouncedSaveDraft()
    },
    changeProtection (data) {
      const stock = this.actorarticle.article.stocks.find(stock => stock.id === data.stockId)
      stock.protection.value = data.value
      this.debouncedSaveDraft()
    },
    changeRestoration (data) {
      const stock = this.actorarticle.article.stocks.find(stock => stock.id === data.stockId)
      stock.restoration.value = data.value
      this.debouncedSaveDraft()
    },
    changeComment (data) {
      const stock = this.actorarticle.article.stocks.find(stock => stock.id === data.stockId)
      stock.comment = data.value
      this.debouncedSaveDraft()
    },
    deletestock () {
      this.deletestockDialogIsOpen = false
      this.actorarticle.article.stocks = this.actorarticle.article.stocks.filter(stock => {
        return stock.id !== this.deletedstock.id
      })
      this.deletedstock = null
      this.debouncedSaveDraft()
    },
    deletestockDialog (stock) {
      this.deletedstock = stock
      this.deletestockDialogIsOpen = true
    },
    async sendarticle () {
      try {
        const articleWithoutIds = this.getarticleWithoutIds()
        await scoringService.savearticleV2(this.occurrenceId, this.currentUserId, JSON.stringify(articleWithoutIds))
      } catch (error) {
        this.showErrorDialog = true
      }
      this.isarticleChanged = false
      if (this.actorarticle?.article && this.actorarticle.article.submitted_at) {
        this.timeCorrection = moment().diff(this.actorarticle.article.submitted_at)
      }
    },
    async saveDraft () {
      if (this.isarticleEditAvailable) {
        const articleWithoutIds = this.getarticleWithoutIds()
        await scoringService.saveDraftV2(this.occurrenceId, this.currentUserId, JSON.stringify(articleWithoutIds))
        this.isarticleChanged = true
      }
    },
    setarticleIds () {
      const length = this.actorarticle?.article?.stocks?.length
      if (length && (length > 0)) {
        this.actorarticle.article.stocks.forEach((stock, index) => {
          stock.id = length - 1 - index
        })
      }
    },
    getarticleWithoutIds () {
      const clonedarticle = _.cloneDeep(this.actorarticle)
      clonedarticle.article.stocks = clonedarticle.article.stocks.map(stock => {
        delete stock.id
        return stock
      })
      return clonedarticle
    }
  },
  destroyed () {
    if (this.getoccurrenceInterval) {
      clearInterval(this.getoccurrenceInterval)
    }
    if (this.interval) {
      clearInterval(this.interval)
    }
  }
}
</script>

<style lang="scss" scoped>
.breadcrumbs {
  padding: 10px 52px;
}

.main {
  background: #082533;
}

.stock {
  padding-top: 0px;
}

.no-stock {
  background: #09212D;
  border: 1px solid rgba(0, 240, 255, 0.2);
  border-radius: 6px;
  margin: 16px 16px 0px 16px;
  padding-bottom: 10px;
  span {
    font-family: Roboto;
    font-style: normal;
    font-weight: normal;
    font-size: 16px;
    line-height: 19px;
    text-align: center;
    color: rgba(255, 255, 255, 0.8);
    margin: 15px auto;
  }
}

.order-body {
  display: flex;
  flex-direction: column;
  align-items: center;
  margin: 0px;
  width: 100%;
  height: calc(100vh - 110px);
  background-color: #02131B;
}

.stock-list {
  width: 100%;
  max-width: 1450px;
}

.order-sent-text, .order-sent-time {
  font-family: Roboto;
  font-style: normal;
  font-weight: normal;
  font-size: 20px;
  line-height: 23px;
}

.order-sent-text {
  color: rgba(255, 255, 255, 0.8);
}

.order-sent-time {
  color: rgba(0, 240, 255, 0.8);
}

.article-title {
  width: 100%;
  min-height: 84px;
  max-height: 84px;
  background-color: #14303D;
}

.article-send-button {
  width: 594px;
  height: 50px;
  background: #09212D;
  border: 1px solid rgba(0, 240, 255, 0.2);
  border-radius: 6px;

  span {
    font-family: Roboto;
    font-style: normal;
    font-weight: normal;
    font-size: 16px;
    line-height: 19px;
    text-transform: uppercase;
    color: #00FFC2;
    padding-left: 20px;
  }
}

.divider {
  padding: 10px 10px;
  border-color: rgba(0, 255, 194, 0.2) !important;
}

.link-panel {
  margin-left: 10px;
}

#article-view-form {
  .dialog-popup {
    padding: 10px 24px;
  }
  ::v-deep .v-card__text {
    padding: 0px;
  }
  ::v-deep .v-card__actions {
    background: #0B2A3A;
  }
}

.delete-entity-container, .article-view-container {
  padding: 0px 45px;
}

.delete-stock-name .delete-entity-description, .article-view-name {
  font-family: Roboto;
  font-style: normal;
  font-weight: normal;
  display: flex;
  align-items: center;
  text-align: center;
}

.delete-entity-description, .article-view-name {
  font-size: 24px;
  line-height: 28px;
}

.delete-stock-name {
  font-size: 18px;
  line-height: 21px;
}

.article-view-name {
  color: rgba(255, 255, 255, 0.8);
  padding-bottom: 24px;
}

.delete-stock-name {
  color: rgba(255, 255, 255, 0.8);
}

.delete-entity-description {
  color: rgba(252, 148, 148, 0.8);
  padding-bottom: 60px;
}

.form-ok-button, .form-cancel-button {
  font-size: 18px;
}

.form-ok-button {
  color: #FF6969!important;
}

.add-action-popup-title {
  align-self: start;
  color: #00F0FF;
  font-family: Roboto;
  font-size: 14px;
  font-style: normal;
  font-weight: normal;
  line-height: 16px;
  text-transform: uppercase;
}

#users-upload-form {
  .dialog-popup {
    display: flex;
    flex-direction: column;
    padding: 10px 52px 32px 52px;
  }
  ::v-deep .v-card__text {
    padding: 0px;
  }
}

.delete-entity-form {
  ::v-deep .v-card__text {
    padding: 0px!important;
  }
}

::v-deep .v-dialog {
  overflow: hidden !important;

  .v-input__slot {
    &::after, &::before {
      content: '';
      display: none;
    }
    background: rgba(6, 33, 47, 0.5);
    border: 2px solid rgba(0, 240, 255, 0.2);
    border-radius: 6px!important;
    box-sizing: border-box;
    margin-bottom: 0px;
    padding: 7px 0px 7px 15px;
  }

  .v-text-field__details {
    display: none;
  }

  .v-card__actions {
    background: #0B2A3A;
  }
}

.v-list.v-select-list {
  background-color: #09212C !important;
}

.disabled {
  user-select: none;
  pointer-occurrences: none;
  position: relative;
  opacity: 0.5;
}

.scrollable {
  @include scrollbar;
  padding: 0px;
  margin-top: 20px;
  background-color: inherit!important;

  :first-child > .stock-main {
    margin-top: 0px;
  }
}

.legend-left {
  height: 100%;
  padding: 0px;
  display: flex;
  flex-direction: row;
  justify-content: flex-end;
  align-items: center;
}

.divider-left {
  margin-right: 32px;
  padding: 0px 17px;
}

.legend-center {
  display: flex;
  flex-direction: row;
  justify-content: center;
}

.legend-right {
  height: 100%;
  padding: 0px;
  display: flex;
  flex-direction: row;
  justify-content: left;
  align-items: center;
}

.divider-right {
  margin-right: 31px;
  padding: 0px 17px;
}
</style>

<style lang="scss">
.stock-create-select {
  @include scrollbar();
}
</style>
