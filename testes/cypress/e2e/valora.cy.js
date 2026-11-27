describe('Valora - verificações básicas', () => {
  it('exibe a tela de login', () => {
    cy.visit('/login');
    cy.contains(/entrar|login/i).should('be.visible');
    cy.get('input[name="login"]').should('exist');
    cy.get('input[name="senha"]').should('exist');
  });

  it('redireciona visitante ao tentar abrir uma área protegida', () => {
    cy.clearCookies();
    cy.visit('/fichas');
    cy.location('pathname').should('include', '/login');
  });

  const login = Cypress.env('VALORA_TEST_USER');
  const senha = Cypress.env('VALORA_TEST_PASSWORD');
  if (login && senha) {
    it('permite que o usuário de teste entre e abra o painel', () => {
      cy.visit('/login');
      cy.get('input[name="login"]').type(login);
      cy.get('input[name="senha"]').type(senha, { log: false });
      cy.get('form').contains(/entrar/i).click();
      cy.location('pathname').should('include', '/dashboard');
      cy.contains(/dashboard/i).should('be.visible');
    });
  } else {
    it.skip('login autenticado - configure VALORA_TEST_USER e VALORA_TEST_PASSWORD no ambiente', () => {});
  }
});
