// src/components/Logout.js
import React, { useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import api from '../api';

const Logout = () => {
  const navigate = useNavigate();

  useEffect(() => {
    const handleLogout = async () => {
      try {
        // Make a request to the logout endpoint
        await api.post('/logout');

        // Remove the token from localStorage
        localStorage.removeItem('authToken');

        // Redirect to the login page
        navigate('/login');
      } catch (error) {
        console.error('Logout error:', error);
        localStorage.removeItem('authToken');
        // Optionally handle the error case
        navigate('/login');
      }
    };

    handleLogout();
  }, [navigate]);

  return <div>Logging out...</div>;
};

export default Logout;
