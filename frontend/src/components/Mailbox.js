import React, { useState, useEffect, useRef } from 'react';
import api from '../api';

const Mailbox = (props) => {
    const provider = props.provider;
    const oauth_id = props.oauth_id;

    const [folders, setFolders] = useState([]);
    const [emails, setEmails] = useState([]);
    const [selectedFolder, setSelectedFolder] = useState(null);
    const [currentPage, setCurrentPage] = useState(1);
    const [totalEmails, setTotalEmails] = useState(0);
    const [loadingEmails, setLoadingEmails] = useState(true);
    const [loadingFolders, setLoadingFolders] = useState(true);
    
    // Ref to hold the current value of selectedFolder
    const selectedFolderRef = useRef(selectedFolder);
    const currentPageRef = useRef(currentPage);

    // Fetch folders when component mounts
    const fetchFolders = async () => {
        setLoadingFolders(true);
        try {
            const response = await api.get(`/${provider}/folders`);
            setFolders(response.data.folders);
            // Set the first folder as selected on load
            if (response.data.folders.length > 0) {
                const firstFolder = response.data.folders[0];
                if (!selectedFolder) {
                    setSelectedFolder(firstFolder.id);
                    selectedFolderRef.current = firstFolder.id; // Update the ref
                    fetchEmails(firstFolder.id);
                }
            }
        } catch (error) {
            console.error('Error fetching folders', error);
        }
        setLoadingFolders(false);
    };

    // Fetch emails based on selected folder
    const fetchEmails = async (folderId, page = 1) => {
        setLoadingEmails(true);
        try {
            const response = await api.get(`/${provider}/emails/${folderId}?page=${page}`);
            setEmails(response.data.emails);
            setCurrentPage(response.data.current_page);
            currentPageRef.current = response.data.current_page;
            setTotalEmails(response.data.total);
        } catch (error) {
            console.error('Error fetching emails', error);
        }
        setLoadingEmails(false);
    };

    // Load folders on mount and start SSE stream
    useEffect(() => {
        fetchFolders();

        // Start SSE stream
        const eventSource = new EventSource(`${process.env.REACT_APP_API_URL}/${oauth_id}/${provider}/sse`);
        eventSource.onmessage = (event) => {
            const data = JSON.parse(event.data);
            const folder_ids = data || [];

            if (Array.isArray(folder_ids) && folder_ids.includes('sync_folders')) {
                fetchFolders();
            } 
            
            // Check against the current value of selectedFolderRef
            if (selectedFolderRef.current 
                && Array.isArray(folder_ids) 
                && folder_ids.includes(selectedFolderRef.current) 
                && currentPageRef.current == 1
            ) {
                fetchEmails(selectedFolderRef.current);
            } 
        };

        eventSource.onerror = (error) => {
            console.error('SSE connection error:', error);
            eventSource.close();
        };

        // Cleanup on unmount
        return () => {
            eventSource.close();
        };
    }, []); // Run once on mount

    // Handle folder selection
    const handleFolderClick = (folderId) => {
        setSelectedFolder(folderId);
        selectedFolderRef.current = folderId; // Update the ref
        fetchEmails(folderId);
    };

    const totalPages = Math.ceil(totalEmails / 10); // Assuming 10 emails per page

    const handleNextPage = () => {
        if (currentPage < totalPages) {
            fetchEmails(selectedFolder, currentPage + 1);
        }
    };

    const handlePrevPage = () => {
        if (currentPage > 1) {
            fetchEmails(selectedFolder, currentPage - 1);
        }
    };

    return (
        <div style={{ display: 'flex' }}>
            {/* Folders Panel */}
            <div style={{ width: '20%', borderRight: '1px solid gray', padding: '0 20px' }}>
                <h3>Folders {loadingFolders && <span style={{ float: 'right', color: '#55af55' }}>Loading...</span> }</h3>
                <ul>
                    {folders.map((folder, index) => (
                        <li
                            key={folder.id}
                            onClick={() => handleFolderClick(folder.id)} // Assuming folder has an id
                            style={{ cursor: 'pointer', padding: '5px 0' }}
                        >
                            {folder.name}
                        </li>
                    ))}
                </ul>
            </div>

            <div style={{ width: '80%', padding: '0 20px' }}>
                <h3>Emails {loadingEmails && <span style={{ float: 'right', color: '#55af55' }}>Loading...</span> }</h3> 
                <hr />                
                {totalEmails ? (
                        <ul>
                            {emails.map((email, index) => (
                                <li key={email.id}>
                                    <h4>{email.subject}</h4>
                                    <span>Date: {email.date}</span> &nbsp; &nbsp;
                                    <span>From: {email?.from[0]?.mail}</span> &nbsp; &nbsp;
                                    <span>Flags: {email?.flags}</span> &nbsp; &nbsp;
                                    <hr />
                                </li>
                            ))}
                        </ul>
                    ) : (<p>No messages yet</p>)
                }
                <div>
                    <button onClick={handlePrevPage} disabled={currentPage === 1}>
                        Previous
                    </button>
                    <span style={{ padding: '10px' }}>Page {currentPage} of {totalPages}</span>
                    <button onClick={handleNextPage} disabled={currentPage === totalPages}>
                        Next
                    </button>
                </div>
            </div>
        </div>
    );
};

export default Mailbox;
