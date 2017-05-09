import React, { Component } from 'react';

export default class ChatBox extends Component {
  render() {
    return (
      <div className='popup-box'>
        <div className='popup-head'>Title</div>
        <div className='popup-messages'>Messages</div>
        <input className='popup-message-input' type='text'></input>
      </div>
    );
  }
}
