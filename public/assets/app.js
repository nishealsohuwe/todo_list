const { createApp, ref, computed } = Vue;

createApp({
  setup() {
    // Состояние приложения
    const auth = ref({ email: '', password: '' });
    const token = ref(localStorage.getItem('token') || '');
    const tasks = ref([]);
    const newTask = ref({ title: '', description: '', status: 'в работе' });
    const pagination = ref({
        currentPage: 1,
        perPage: 10,
        total: 1
    });

    // Проверка авторизации
    const isAuthenticated = computed(() => !!token.value);

    // Методы для работы с API
    const apiRequest = async (url, method = 'GET', body = null) => {
      const headers = {
        'Content-Type': 'application/json',
      };

      if (token.value) {
        headers['Authorization'] = `Bearer ${token.value}`;
      }

      const response = await fetch(url, {
        method,
        headers,
        body: body ? JSON.stringify(body) : null,
      });

      return await response.json();
    };

    // Авторизация
    const register = async () => {
      const data = await apiRequest('/register', 'POST', {
        email: auth.value.email,
        password: auth.value.password,
      });
      if (data.token) {
        token.value = data.token;
        localStorage.setItem('token', data.token);
      }
    };

    const login = async () => {
      const data = await apiRequest('/login', 'POST', {
        email: auth.value.email,
        password: auth.value.password,
      });
      if (data.token) {
        token.value = data.token;
        localStorage.setItem('token', data.token);
        loadTasks();
      }
    };

    const logout = () => {
      token.value = '';
      localStorage.removeItem('token');
    };

    // Работа с задачами
    const loadTasks = async () => {
        const response = await apiRequest(
            `/tasks?page=${pagination.value.currentPage}&per_page=${pagination.value.perPage}`
        );
        tasks.value = response.tasks.map(task => ({
            ...task,
            showDescription: false
        }));
        pagination.value.total = response.pagination.total;
    };    

    const addTask = async () => {
      await apiRequest('/tasks', 'POST', newTask.value);
      newTask.value = { title: '', description: '', status: 'в работе' };
      await loadTasks();
    };

    const updateTask = async (task) => {
      await apiRequest(`/tasks/${task.id}`, 'PUT', {
        title: task.title,
        description: task.description,
        status: task.status,
      });
    };

    const deleteTask = async (id) => {
      await apiRequest(`/tasks/${id}`, 'DELETE');
      await loadTasks();
    };

    // Метод для смены страницы
    const changePage = (page) => {
        pagination.value.currentPage = page;
        loadTasks();
    };

    // Метод для изменения количества задач
    const changePerPage = (value) => {
        pagination.value.perPage = value;
        pagination.value.currentPage = 1; // Сбрасываем на первую страницу
        loadTasks();
    };

    // Загружаем задачи при старте, если пользователь авторизован
    if (isAuthenticated.value) {
      loadTasks();
    }

    return {
      auth,
      token,
      tasks,
      newTask,
      isAuthenticated,
      pagination,
      register,
      login,
      logout,
      addTask,
      updateTask,
      deleteTask,
      changePage,
      changePerPage
    };
  },
}).mount('#app');