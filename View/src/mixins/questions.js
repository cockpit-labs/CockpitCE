import { isEmpty } from 'lodash'
import ExpressionLanguage from 'expression-language'

export default {
  props: {
    question: Object,
    readOnly: Boolean
  },

  methods: {
    getChoices () {
      if (isEmpty(this.question.answers)) {
        return null
      }
      return this.question.answers.map(v => v.choice)
    },

    setChoices (choices) {
      if (choices === null) {
        this.question.answers = []
      } else if (Array.isArray(choices)) {
        this.question.answers = choices.map(v => {
          return { choice: { id: v }, rawValue: null, value: this.computeValue(v) }
        })
      } else {
        this.question.answers = [{ choice: choices, rawValue: null, value: this.computeValue(choices) }]
      }

      this.emitUpdate()
    },

    getRawValue () {
      if (isEmpty(this.question.answers)) {
        return null
      }
      const rawValues = this.question.answers.map(v => v.rawValue)
      return rawValues.length === 1 ? rawValues[0] : rawValues
    },

    setRawValue (value) {
      if (value === null) {
        this.question.answers = []
      } else {
        value = String(value)

        if (isEmpty(this.question.answers)) {
          this.question.answers = [{ rawValue: value }]
        }
        this.question.answers[0].rawValue = value
        this.question.answers[0].choice = this.question.choices[0].id
        this.question.answers[0].value = this.computeValue(this.question.choices[0].id, value)
      }
      this.emitUpdate()
    },

    emitUpdate () {
      this.$emit('update:question', this.question)
    },

    computeValue (choiceId, rawValue = null) {
      const expressionLanguage = new ExpressionLanguage()
      let value = 0
      try {
        const formula = this.question.choices.find(c => c.id === choiceId)?.valueFormula?.expression
        value = expressionLanguage.evaluate(formula, { value: rawValue })
      } catch (error) {
        if (typeof rawValue === 'number') {
          value = rawValue
        }
      }
      return String(value)
    }
  }
}
