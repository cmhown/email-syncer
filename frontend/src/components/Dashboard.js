import React, { useContext } from 'react';
import { Link } from 'react-router-dom';
import { AuthContext } from '../AuthContext';
import Mailbox from './Mailbox';

const Dashboard = () => {

  const { user } = useContext(AuthContext);

  const oauth_accounts = user?.oauth_accounts || [];

  // console.log(user);

  const handleOAuthLink = provider => {

    // Redirect to the OAuth redirect route
    window.location.href = `auth/${provider}/redirect`;
  };

  return (
    <div>
      <h2>Dashboard
        <span style={{float:"right", fontSize: "16px"}}>
          <Link to='/logout'>Logout</Link>
        </span>
    </h2>
    <hr></hr>


      {(oauth_accounts.length > 0) &&
        user?.oauth_accounts.map((oauth_account, index) => (
          <div key={oauth_account.id}>
            <br></br>
            <h3 style={{ textAlign: "center" }}>{oauth_account.provider.toUpperCase()}</h3>
            <Mailbox provider={oauth_account.provider} oauth_id={oauth_account.id} ></Mailbox>
            <br></br>
            <br></br>
          </div>
        ))
      }
      {(oauth_accounts.length == 0) && <h3>No account linked. Please link an account to view the Mailbox</h3>}

      <br></br>
      <br></br>


      <button onClick={() => handleOAuthLink('microsoft')}>Link Microsoft Account</button>
      &nbsp; &nbsp; &nbsp;
      <button onClick={() => handleOAuthLink('google')}>Link Google Account</button>

    </div>
  );
};

export default Dashboard;
