describe('Smoke Test', () => {
  it('should load the homepage', () => {
    cy.visit('/')
    cy.contains('Analyt Loan')
  })
})
