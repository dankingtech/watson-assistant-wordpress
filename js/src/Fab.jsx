import React, { Component } from 'react';

export default class Fab {
  componentWillLeave(callback) {
    setTimeout(callback, 300);
  }

  render() {
    return (
      <div
        id='watson-fab' 
        class='drop-shadow animated' 
        onClick={this.props.openChat}
      >
        <span id='watson-fab-icon' class='dashicons dashicons-format-chat'></span>
      </div>
    );
  }
};