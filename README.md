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

## 📘 Descrição Geral

O **EcoMonitor** é um sistema web criado com o objetivo de registrar, acompanhar e analisar dados de consumo energético e geração de energia solar em residências. Com a constante alta no custo da energia elétrica e a crescente adoção de fontes renováveis, como a energia solar, torna-se essencial fornecer aos consumidores uma ferramenta simples e eficaz que permita a visualização clara de seus gastos, economia e retorno sobre o investimento. A proposta do sistema surgiu como solução acadêmica com potencial de aplicação real no auxílio ao planejamento energético domiciliar.

---

## 🎯 Objetivos do Projeto

### Objetivo Geral:
Desenvolver um sistema acessível que permita o monitoramento de consumo e geração de energia solar em residências.

### Objetivos Específicos:
- Registrar dados diários de consumo e geração solar;
- Calcular automaticamente a economia mensal com base na tarifa configurável;
- Gerar relatórios em PDF para visualização ou impressão;
- Exportar os dados para outras ferramentas;
- Permitir comparação entre diferentes residências.

---

## 🚧 Requisitos

### ✅ Requisitos Funcionais:
- Inserção de dados de consumo energético, tarifa e geração solar;
- Cálculo automático de economia energética e retorno sobre investimento (payback);
- Armazenamento das informações em banco de dados relacional (MySQL);
- Geração de relatórios a partir dos dados registrados.

### 🔁 Requisitos Não Funcionais:
- Backend em PHP;
- Scripts de cálculo planejados para futura integração com Python;
- Estrutura modular e versionada via GitHub;
- Código limpo, documentado e estruturado para manutenção.

### 📐 Regras de Negócio:
- O cálculo mensal considera um mês fixo de 30 dias;
- Todos os valores numéricos devem ser positivos e preenchidos corretamente.

---

## 🔁 Modelo de Processo Utilizado

Adotou-se o modelo **incremental com prototipação evolutiva**, permitindo ciclos iterativos de desenvolvimento. Isso garantiu flexibilidade para inserir ajustes durante a implementação e organizar o desenvolvimento em etapas: inserção de dados → geração de cálculo → criação de relatórios e visualização.

---

## 💻 Tecnologias Utilizadas

- PHP (Backend)  
- MySQL (Banco de dados)  
- HTML + CSS (Frontend)  
- FPDF (Geração de relatórios)  
- Git + GitHub (Controle de versão)

---

## 🚀 Como Executar o Projeto

1. Clone o repositório:
```
git clone https://github.com/seuusuario/ecomonitor.git
```
2. Importe o banco de dados no MySQL;

3. Configure a conexão no arquivo conexao.php;

4. Inicie o servidor Apache (XAMPP ou outro);

5. Acesse no navegador:
```
http://localhost/pa_final_corrigido/index.php
```
---

## 📎 Licença
- Este projeto foi desenvolvido com fins acadêmicos e pode ser reutilizado para fins educacionais e experimentais.



