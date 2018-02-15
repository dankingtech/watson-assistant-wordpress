import React from 'react';

let {text, iconPos} = watsonconvSettings.fabConfig;

const Fab = ({openChat}) => (
  <div
    id='watson-fab' 
    class='drop-shadow animated' 
    onClick={openChat}
  >
    {iconPos === 'left' && <span id='watson-fab-icon' class='dashicons dashicons-format-chat'></span>}
    {text && <span id='watson-fab-text'>{text}</span>}
    {iconPos === 'right' && <span id='watson-fab-icon' class='dashicons dashicons-format-chat'></span>}
  </div>
);

export default Fab;