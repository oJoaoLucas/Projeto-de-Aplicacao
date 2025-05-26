# 🌞 EcoMonitor

### 📘 Projeto de Aplicação – Engenharia de Software I  
**Curso:** Sistemas de Informação – FHO  
**Turma:** 5º Período B  
**Professor:** Camilo César Perucci

---

## 👥 Integrantes do Grupo

- Alefe Cirino – RA: 113656  
- Gustavo Henrique Timachi – RA: 114975  
- Gustavo Rodrigues – RA: 114574  
- João Lucas Lima – RA: 113059  
- Márcio Junior – RA: 115380  
- Matheus Nogueira – RA: 113708  

---

## 📝 1. Definição do Escopo do Projeto

**Cenário de Negócio:**  
Com o aumento constante do custo da energia elétrica e o incentivo ao uso de fontes renováveis como a energia solar, cresce a demanda por ferramentas que ajudem consumidores a entender e otimizar seu consumo. Acompanhamentos manuais dificultam a análise de economia real e o planejamento. O EcoMonitor surge como solução simples e acessível para organizar, registrar e apresentar essas informações.

**Objetivo do Projeto:**  
Desenvolver um sistema web para registrar e acompanhar consumo e geração solar, calcular economia mensal, e gerar relatórios prontos para visualização ou impressão.

**Restrições:**
- Sem gráficos ou visualizações avançadas na versão inicial;
- Relatórios simples com opção de impressão;
- Backend modular em PHP com possibilidade de integração com Python;
- Sem login de usuários nesta primeira versão.

---

## 🔁 2. Estratégia para o Desenvolvimento do Projeto

**Modelo de Processo:** Ciclo de Vida Incremental com Prototipagem.  
Desenvolvimento em fases: inserção de dados → geração de cálculo → expansão futura com dashboards e gráficos.

**Justificativa:**  
Permite ajustes constantes durante o desenvolvimento e reduz riscos ao incorporar feedback de forma evolutiva.

---

## ✅ 3. Requisitos

### Requisitos Funcionais
- Inserção de dados de consumo energético diário e geração solar.
- Cálculo automático de economia.
- Armazenamento em banco de dados relacional.
- Geração de relatórios com os dados e cálculos.

### Requisitos Não-Funcionais
- Uso de MySQL como banco de dados.
- Backend em PHP.
- Scripts Python planejados para futuras versões.
- Versionamento com GitHub.
- Código organizado e comentado.

### Regras de Negócio
- Base de cálculo: 30 dias/mês.
- Validação de campos numéricos (sem valores negativos ou nulos).

---

## 📌 4. User Story (Especificação Ágil)

**User Story 1 – Inserção de Dados**  
> Como usuário,  
> Quero inserir meus dados de consumo, tarifa de energia e geração solar,  
> Para que o sistema calcule meu gasto e economia.

**Tasks:**
- Criar formulário com campos de entrada
- Validar campos obrigatórios
- Salvar dados no banco

---

## 🖥️ Tecnologias Utilizadas

- PHP (backend)
- MySQL (banco de dados)
- HTML/CSS (frontend)
- FPDF (geração de relatórios em PDF)
- Python (planejado para cálculos avançados)
- Git/GitHub (versionamento)

---

## 📎 Licença

Este projeto é de uso acadêmico e livre para fins educacionais.
