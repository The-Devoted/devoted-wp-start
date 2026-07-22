/**
 * Ambient declarations so TypeScript accepts side-effect style imports
 * (`import './style.scss'`). @wordpress/scripts handles the actual bundling.
 */
declare module '*.scss';
declare module '*.css';
