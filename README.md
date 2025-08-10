![MoonVibe](https://github.com/user-attachments/assets/c3a30a2c-7376-4db5-98c7-8890353d2a77)

# 🚀 MoonVibe - MoonShine AI Admin Generator

> **From idea to ready admin panel in 5 minutes. Seriously.**

Forget about days of admin panel development! MoonVibe is an AI-powered tool for instantly creating fully functional Laravel admin panels with [MoonShine](https://getmoonshine.app/). Simply describe what your admin panel should be like - and get a complete solution with database, models, migrations, and beautiful interface.

![2025-07-06_13-21-38](https://github.com/user-attachments/assets/06edf340-636c-4555-aa2e-c0dc0be6c463)

## ✨ Quick Start

> The project runs only in Docker.

> To use this package you need API tokens for OpenRouter/OpenAI/DeepSeek

### Installation
1. Clone the repository:
```bash
git clone git@github.com:moonshine-software/moon-vibe.git
```
2. Copy `.env.example` to `.env`
3. Install `UID` and `GID` user of your OS
4. Add `OPEN_ROUTER_TOKEN` and/or `OPENAI_API_KEY`/`DEEP_SEEK_TOKEN` to the `.env` file for API requests
5. Run:
```bash
# Quick setup with Make
make init

# Then you can run the project with the following command:
make up
```
6. Go to http://localhost:80
7. Use for login: `admin@mail.com/12345`
8. Navigate to the **LLM** section and add your API model (for OpenAI, for example `gpt-4.1-mini`, or for OpenRouter, copy the name of the model, for example `openai/gpt-4.1`)
![2025-07-06_16-01-52](https://github.com/user-attachments/assets/dcf1616d-5bcf-45fb-89a3-05b51008bc20)
9. You can now go to the **Generation** section and start using the application

### **Generate Schema**
After configuring your LLM provider, you are ready to generate your admin panel schema:
- Go to the "Generation" section.
- Enter a name for your new project.
- Select an LLM model from the list of connected providers.
- Describe your project in detail in the provided field. The more specific and detailed your description, the higher the quality of the generated admin panel. Specify entities, relationships between them, required fields, user roles, and any special business logic.
- Click the "Start Generation Schema" button.

MoonVibe will automatically analyze your requirements and generate a complete admin panel structure: database schemas, models, migrations, and MoonShine resources. You can track the generation progress in real time. Once it’s finished, you can preview or download your ready-to-use project—or instantly test its functionality right from the MoonVibe interface by clicking the "Testing project" button.

> You can watch the log of your request by completing the command in the console`make logs`
> 
![2025-07-06_13-24-32](https://github.com/user-attachments/assets/eb57d793-c50b-41ec-a9c5-e371061ca432)

## 🎯 What is MoonShine AI Admin Generator?

This application revolutionizes the way you create Laravel admin panels by combining the power of **MoonShine 3** with **artificial intelligence**. Simply describe your project requirements in natural language, and watch as our AI generates a complete, working admin panel with:

- **Database schemas** with proper relationships
- **Eloquent models** with validations and relationships
- **Database migrations** ready for deployment
- **MoonShine resources** with forms, tables, and filters
- **Complete Laravel project** packaged and ready to deploy

![2025-07-06_13-26-47](https://github.com/user-attachments/assets/09e115a4-14e8-41f8-8ae5-aae5c4ac318a)

### 🤖 AI-Powered Generation

- **Multiple LLM Providers**: OpenAI GPT models, DeepSeek, and more
- **Intelligent Schema Validation**: Ensures generated code follows Laravel and MoonShine best practices
- **Iterative Correction**: AI can fix validation errors automatically
- **Natural Language Processing**: Describe your admin panel in plain languge


### 🏗️ Build System

- **Automated Project Assembly**: Creates complete Laravel projects with MoonShine pre-installed
- **Tar Archive Generation**: Download ready-to-deploy projects
- **Test Environment Setup**: Spin up test instances for immediate preview
- **Repository Integration**: Clone from custom Laravel templates


## 🌟 Key Features

### 🎨 **AI Schema Generation**

- Natural language to admin panel conversion
- Support for complex relationships (BelongsTo, HasMany, BelongsToMany)
- Automatic field type detection and validation
- Smart naming conventions and best practices

### 👥 **User Management**

- Role-based access control (Admin/User)
- Subscription plans with generation limits
- Multi-language support
- Profile management with custom settings

### 🔧 **Project Management**

- Multiple projects per user
- Schema versioning and history
- Real-time build progress tracking
- Error handling and validation feedback

### **Technology Stack**
- **Backend**: Laravel 12, PHP 8.4+
- **Admin Panel**: MoonShine 3
- **AI Integration**: OpenAI API, DeepSeek API
- **Real-time**: Centrifugo WebSocket
- **Queue System**: Laravel Queues
- **Database**: MySQL

### **LLM Providers Setup**

1. **OpenAI**: [OpenAI Platform](https://platform.openai.com)
2. **DeepSeek**: [DeepSeek Platform](https://platform.deepseek.com)
2. **OpenRouter**: [OpenRouter Platform](https://openrouter.ai)

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](https://choosealicense.com/licenses/mit) for details.

## 🆘 Support

- **Issues**: [GitHub Issues](https://github.com/moonshine-software/moon-vibe/issues)

