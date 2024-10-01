import React, { createContext, useState, useEffect } from 'react';
import api from './api';

export const AuthContext = createContext();

export const AuthProvider = ({ children }) => {
  const [authToken, setAuthToken] = useState(() => localStorage.getItem('authToken'));
  const [user, setUser] = useState(null);

  useEffect(() => {
    if (authToken) {
      // Optionally, fetch user data from the API
      api.get('/user') // Make sure you have a route to get authenticated user
        .then(response => setUser(response.data))
        .catch(error => {
          console.error('Failed to fetch user:', error);
          setAuthToken(null);
          localStorage.removeItem('authToken');
        });
    }
  }, [authToken]);

  const login = token => {
    setAuthToken(token);
    localStorage.setItem('authToken', token);
  };

  const logout = () => {
    setAuthToken(null);
    setUser(null);
    localStorage.removeItem('authToken');
  };

  return (
    <AuthContext.Provider value={{ authToken, user, login, logout }}>
      {children}
    </AuthContext.Provider>
  );
};
